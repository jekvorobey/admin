<?php

namespace App\Http\Controllers\Customers\Detail;


use App\Http\Controllers\Controller;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Customer\Services\ReferralService\Dto\GetPromotionDto;
use Greensight\Customer\Services\ReferralService\Dto\PutPromotionDto;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Greensight\Marketing\Dto\Price\PricesInDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\OfferService\OfferService;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TabPromoProductController extends Controller
{
    public function load($id)
    {
        return response()->json([
            'promoProducts' => $this->loadPromotionProducts($id),
        ]);
    }

    /**
     * @param $id
     * @param ProductService $productService
     * @param ReferralService $referralService
     * @return \Illuminate\Http\JsonResponse
     */
    public function save($id, ProductService $productService, ReferralService $referralService)
    {
        $data = $this->validate(request(), [
            'product_id' => 'required|integer',
            'active' => 'integer',
            'files' => 'nullable',
            'description' => 'required'
        ]);

        $product = $productService->newQuery()
        ->setFilter('id', $data['product_id'])
        ->products();
        if ($product->isEmpty())
        {
            throw new BadRequestHttpException('Ошибка: товар не найден');
        }

        $referralService->putPromotions((new PutPromotionDto())
            ->setCustomerId($id)
            ->setProductId($data['product_id'])
            ->setActive((bool)$data['active'])
            ->setFiles(request('files', []))
            ->setDescription($data['description'])
        );

        return response()->json([
            'promoProducts' => $this->loadPromotionProducts($id),
        ]);
    }

    public function export($id)
    {
        $writer = WriterFactory::createFromType(Type::XLSX);

        $writer->openToBrowser("Товары для продвижения {$id}.xlsx");

        $writer->addRow(WriterEntityFactory::createRowFromArray([
            'Товар',
            'Бренд',
            'Категория',
            'Цена',
            'Описание',
            'Файлы',
            'Дата создания',
            'Дата архивации',
        ], null));
        $promoProducts = $this->loadPromotionProducts($id);
        foreach ($promoProducts as $promoProduct) {
            $files = [];
            foreach ($promoProduct['files'] as $file) {
                $files[] = url(FileDto::linkById($file));
            }
            $writer->addRow(WriterEntityFactory::createRowFromArray([
                $promoProduct['product_name'],
                isset($promoProduct['brand']) ? $promoProduct['brand']['name'] : '',
                isset($promoProduct['category']) ? $promoProduct['category']['name'] : '',
                isset($promoProduct['price']) ? (string)$promoProduct['price'] : '',
                $promoProduct['description'],
                join(', ', $files),
                $promoProduct['created_at'],
                !$promoProduct['active'] ? $promoProduct['updated_at'] : '',
            ], null));
        }

        $writer->close();
    }

    protected function loadPromotionProducts($id)
    {
        /** @var ReferralService $referralService */
        $referralService = resolve(ReferralService::class);
        /** @var ProductService $productService */
        $productService = resolve(ProductService::class);
        /** @var OfferService $offerService */
        $offerService = resolve(OfferService::class);
        /** @var PriceService $priceService */
        $priceService = resolve(PriceService::class);

        $promoProducts = $referralService->getPromotions((new GetPromotionDto())->setReferralId($id));

        $product_ids = $promoProducts->pluck('product_id');
        $result = $promoProducts->toArray();
        if ($product_ids->isNotEmpty()) {
            $products = $productService
                ->products(
                    (new RestQuery())
                        ->setFilter('id', $product_ids->all())
                        ->include('brand', 'category')
                        ->addFields(ProductDto::entity(), 'id', 'brand_id', 'category_id', 'name')
                        ->addFields(BrandDto::entity(), 'id', 'name')
                        ->addFields(CategoryDto::entity(), 'id', 'name')
                )
                ->keyBy('id');
            $offers = $offerService->offersByProduct($product_ids->all(), null, true)->pluck('id', 'product_id');

            $prices = $priceService
                ->prices((new PricesInDto())->setOffers($offers->values()->all()))
                ->keyBy('offer_id');

            foreach ($result as &$promoProduct) {
                if ($products->has($promoProduct['product_id'])) {
                    $promoProduct['brand'] = $products[$promoProduct['product_id']]->brand();
                    $promoProduct['category'] = $products[$promoProduct['product_id']]->category();
                    $promoProduct['product_name'] = $products[$promoProduct['product_id']]->name;
                }
                if ($offers->has($promoProduct['product_id'])) {
                    if ($prices->has($offers[$promoProduct['product_id']])) {
                        $promoProduct['price'] = $prices[$offers[$promoProduct['product_id']]]->price;
                    }
                }
            }
        }


        return $result;
    }
}