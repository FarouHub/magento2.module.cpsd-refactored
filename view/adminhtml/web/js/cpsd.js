/**
 * Best4Mage - Configurable Product Simple Details
 * @author Best4Mage
 */
require([
    'jquery',
    'mage/translate'
], function ($,$t) {

    $(document).on('click', 'div[data-index="best4mage-cpsd-settings"] > .fieldset-wrapper-title', function () {
        if(typeof isEnabled != 'undefined' && isEnabled){
            if(typeof isEnableProductLevel != 'undefined' && isEnableProductLevel){

                $('select[name="product[cpsd_preselect_type]"]').change(function (event) {
                    if ($(this).val() == 3) {
                        $('.admin__field[data-index="cpsd_preselect_specific"]').show();
                    } else {
                        $('.admin__field[data-index="cpsd_preselect_specific"]').hide();
                    }
                });

                $('select[name="product[cpsd_preselect_type]"]').trigger('change');

                $('input[name="product[cpsd_tp_use_for_all]"]').change(function (event) {
                    if ($(this).val() == 1) {
                        $('.admin__field[data-index="cpsd_tp_price_type"]').hide();
                        $('.admin__field[data-index="cpsd_tp_tier_product"]').show();
                    } else {
                        $('.admin__field[data-index="cpsd_tp_price_type"]').show();
                        $('.admin__field[data-index="cpsd_tp_tier_product"]').hide();
                    }
                });

                $('input[name="product[cpsd_tp_use_for_all]"]').trigger('change');

                
                $('input[name="product[cpsd_tp_enable]"]').change(function (event) {
                    if ($(this).val() == 0) {
                        $('.admin__field[data-index="cpsd_tp_use_for_all"]').hide();
                        $('.admin__field[data-index="cpsd_tp_price_type"]').hide();
                        $('.admin__field[data-index="cpsd_tp_tier_product"]').hide();
                    } else {
                        $('.admin__field[data-index="cpsd_tp_use_for_all"]').show();
                        $('.admin__field[data-index="cpsd_tp_price_type"]').show();
                        $('.admin__field[data-index="cpsd_tp_tier_product"]').show();
                        $('input[name="product[cpsd_tp_use_for_all]"]').trigger('change');
                    }
                });

                $('input[name="product[cpsd_tp_enable]"]').trigger('change');
            }else{
                var attrId = [
                    'div[data-index="cpsd_enable"]',
                    'div[data-index="cpsd_preselect_type"]',
                    'div[data-index="cpsd_tp_enable"]',
                    'div[data-index="cpsd_tp_price_type"]',
                    'div[data-index="cpsd_tp_use_for_all"]'
                ];
                $.each(attrId, function(index, el) {
                    $(el+' label').addClass('cpsd-hide');
                });

                var noticeHtml = '<div class="cpsd-notice" style="color:#eb5202; padding:40px 0 20px 60px;"><strong>'
                                + $.mage.__('Note :: To use product level options, Please make sure you have activated it from here,')
                                + ' <br/>'
                                + $.mage.__('Stores > Settings - Configuration > BEST4MAGE EXTENSIONS - Best4Mage CPSD > General Settings - Use Product Level Settings.')
                                + '</strong></div>';
                if($('.fieldset-wrapper[data-index="best4mage-cpsd-settings"]').length > 0) {
                    if($('.fieldset-wrapper[data-index="best4mage-cpsd-settings"]').find('.cpsd-notice').length == 0)
                        $('.fieldset-wrapper[data-index="best4mage-cpsd-settings"]').find('.admin__fieldset-wrapper-content').prepend(noticeHtml);
                }

                $('.cpsd-notice').next('.admin__fieldset').find('.cpsd-hide').closest('.admin__field').hide();

                if (typeof preselectType != 'undefined' && preselectType != 3) {
                    $('.admin__field[data-index="cpsd_preselect_specific"]').hide();
                }
                if (typeof isCptpEnable != 'undefined' && !isCptpEnable) {
                    $('.admin__field[data-index="cpsd_tp_tier_product"]').hide();
                } else if  (typeof isUseForAll != 'undefined' && !isUseForAll) {
                    $('.admin__field[data-index="cpsd_tp_tier_product"]').hide();
                }
            }
        } else {
            var noticeHtml = '<div class="cpsd-notice" style="color:#eb5202; padding:40px 0 20px 60px;"><strong>'
                            + $.mage.__('Note :: To use product level options, Please make sure you have activated it from here,')
                            + ' <br/>'
                            + $.mage.__('Stores > Settings - Configuration > BEST4MAGE EXTENSIONS - Best4Mage CPSD > General Settings - Use Product Level Settings.')
                            + '</strong></div>';
            if($('.fieldset-wrapper[data-index="best4mage-cpsd-settings"]').length > 0) {
                if($('.fieldset-wrapper[data-index="best4mage-cpsd-settings"]').find('.cpsd-notice').length == 0)
                    $('.fieldset-wrapper[data-index="best4mage-cpsd-settings"]').find('.admin__fieldset-wrapper-content').prepend(noticeHtml);
            }

            $('.cpsd-notice').next('.admin__fieldset').find('.admin__field').hide();
        }
    });

    $(document).on('click', 'div[data-index="cpsd_settings"] > .fieldset-wrapper-title', function () {
        if((typeof isEnableCat != 'undefined' && !isEnableCat) || (typeof isEnableCategoryLevel != 'undefined' && !isEnableCategoryLevel)){
            var noticeHtml = '<div class="cpsd-notice" style="color:#eb5202; padding:40px 0 20px 60px;"><strong>'
                            + $.mage.__('Note :: To use category level options, Please make sure you have activated it from here,')
                            + ' <br/>'
                            + $.mage.__('Stores > Settings - Configuration > BEST4MAGE EXTENSIONS - Best4Mage CPSD > Category Settings - Use Category Level Settings.')
                            + '</strong></div>';
            if($('.fieldset-wrapper[data-index="cpsd_settings"]').length > 0) {
                if($('.fieldset-wrapper[data-index="cpsd_settings"]').find('.cpsd-notice').length == 0)
                    $('.fieldset-wrapper[data-index="cpsd_settings"]').find('.admin__fieldset-wrapper-content').prepend(noticeHtml);
            }

            $('.cpsd-notice').next('.admin__fieldset').find('.admin__field').hide();
        }
    });
});
