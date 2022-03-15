<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use App\Managers\PromoProducts\PromoProductsManager;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\FileDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Core\PimException;

class TabPromoProductController extends Controller
{
    /**
     * Возвращает список промо-товаров
     * @throws PimException
     */
    public function load(?int $merchantId, PromoProductsManager $promoProductsManager): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        return response()->json([
            'promoProducts' => $promoProductsManager->fetch($merchantId),
        ]);
    }

    /**
     * Обновить/Добавить промо-товар
     * @throws PimException
     */
    public function save(?int $merchantId, Request $request, PromoProductsManager $promoProductsManager): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $data = $this->validate($request, [
            'product_id' => 'required|integer',
            'mass' => 'required|integer',
            'active' => 'integer',
            'files' => 'nullable',
            'description' => 'required',
        ]);

        $promoProductsManager->save($merchantId, $data);

        return response()->json([
            'promoProducts' => $promoProductsManager->fetch($merchantId),
        ]);
    }

    /**
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     * @throws IOException
     * @throws PimException
     */
    public function export($id, PromoProductsManager $promoProductsManager)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

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
        $promoProducts = $promoProductsManager->fetch($id);
        foreach ($promoProducts as $promoProduct) {
            $files = [];
            foreach ($promoProduct['files'] as $file) {
                $files[] = url(FileDto::linkById($file));
            }
            $writer->addRow(WriterEntityFactory::createRowFromArray([
                $promoProduct['product_name'],
                isset($promoProduct['brand']) ? $promoProduct['brand']['name'] : '',
                isset($promoProduct['category']) ? $promoProduct['category']['name'] : '',
                isset($promoProduct['price']) ? (string) $promoProduct['price'] : '',
                $promoProduct['description'],
                join(', ', $files),
                $promoProduct['created_at'],
                !$promoProduct['active'] ? $promoProduct['updated_at'] : '',
            ], null));
        }

        $writer->close();
    }
}
