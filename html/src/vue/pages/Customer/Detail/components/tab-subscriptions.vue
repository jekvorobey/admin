<template>
    <div>
        <div class="row">
            <div class="col-sm-7">
                <div class="card p-4">
                    <div class="card-body">
                        <h5 class="card-title">Подписки пользователя</h5>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="newsletter1" checked>
                            <label class="custom-control-label" for="newsletter1">
                                Новинки лучших мировых брендов
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="newsletter2" checked>
                            <label class="custom-control-label" for="newsletter2">
                                Персональные рекомендации
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="newsletter3">
                            <label class="custom-control-label" for="newsletter3">
                                Избранные статьи от стилистов
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="newsletter4">
                            <label class="custom-control-label" for="newsletter4">
                                Новости бренда R+Co
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="newsletter5" checked>
                            <label class="custom-control-label" for="newsletter5">
                                Новости Украины
                            </label>
                        </div>

                        <button class="btn btn-md btn-success mt-3" disabled>
                            Сохранить изменения
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-sm-5 mb-2">
                <div class="card p-3 mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Периодичность уведомлений</h5>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="daily" name="customRadio" class="custom-control-input" checked>
                            <label class="custom-control-label" for="daily">Каждый день</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="weekly" name="customRadio" class="custom-control-input">
                            <label class="custom-control-label" for="weekly">1 раз в неделю</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="twiceAMonth" name="customRadio" class="custom-control-input">
                            <label class="custom-control-label" for="twiceAMonth">2 раза в месяц</label>
                        </div>
                        <div class="custom-control custom-radio">
                            <input type="radio" id="never" name="customRadio" class="custom-control-input">
                            <label class="custom-control-label" for="never">Никогда</label>
                        </div>
                    </div>
                </div>

                    <div class="card p-3">
                        <div class="card-body">
                            <h5 class="card-title">Предпочитаемый способ связи</h5>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="SMS" checked>
                                <label class="custom-control-label" for="SMS">
                                    SMS
                                </label>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="Email" checked>
                                <label class="custom-control-label" for="Email">
                                    Email
                                </label>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "tab-subscriptions"
    }
</script>

<style scoped>

</style>