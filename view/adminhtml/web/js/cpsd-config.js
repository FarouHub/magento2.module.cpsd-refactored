/**
 * Best4Mage - Configurable Product Simple Details
 * @author Best4Mage
 */
require([
    'jquery'
], function ($,$t) {

    $(document).ready(function() {
        $('#cpsd_tp_settings_enable_cpsd_tp').on('change', function() {
            if ($(this).val() == 0) {
                $('#row_cpsd_tp_settings_use_for_all').hide();
                $('#row_cpsd_tp_settings_price_type').hide();
                $('#row_cpsd_tp_settings_enable_grid_look').hide();
                $('#row_cpsd_tp_settings_enable_qty_autofill').hide();
                $('#row_cpsd_tp_settings_grid_title').hide();
            } else {
                $('#row_cpsd_tp_settings_use_for_all').show();
                if($('#cpsd_tp_settings_use_for_all').val() == 0) {
                    $('#row_cpsd_tp_settings_price_type').show();
                }
                $('#row_cpsd_tp_settings_enable_grid_look').show();
                if($('#cpsd_tp_settings_enable_grid_look').val() == 1) {
                    $('#row_cpsd_tp_settings_enable_qty_autofill').show();
                    $('#row_cpsd_tp_settings_grid_title').show();
                }
            }
        });
        $('#cpsd_tp_settings_enable_cpsd_tp').trigger('change');

        $('#cpsd_category_settings_show_price_range').on('change', function() {
            if($(this).val() == 0) {
                $('#row_cpsd_category_settings_range_label').hide();
            } else {
                $('#row_cpsd_category_settings_range_label').show();
            }
        });
        $('#cpsd_category_settings_switch_price').on('change', function() {
            if($(this).val() == 0) {
                $('#row_cpsd_category_settings_show_tier_price').hide();
                $('#row_cpsd_category_settings_tier_label').hide();
            } else {
                $('#row_cpsd_category_settings_show_tier_price').show();
                $('#cpsd_category_settings_show_tier_price').trigger('change');
            }
        });
        $('#cpsd_category_settings_show_tier_price').on('change', function() {
            if($(this).val() == 0) {
                $('#row_cpsd_category_settings_tier_label').hide();
            } else {
                $('#row_cpsd_category_settings_tier_label').show();
            }
        });
        $('#cpsd_category_settings_switch_name').on('change', function() {
            if($(this).val() == 0) {
                $('#row_cpsd_category_settings_list_name_selector').hide();
            } else {
                $('#row_cpsd_category_settings_list_name_selector').show();
            }
        });
        $('#cpsd_category_settings_switch_url').on('change', function() {
            if($(this).val() == 0) {
                $('#row_cpsd_category_settings_list_url_selector').hide();
            } else {
                $('#row_cpsd_category_settings_list_url_selector').show();
            }
        });
        setTimeout(function() {
            if($('#cpsd_category_settings_enable_cat').val() == 1) {
                $('#cpsd_category_settings_show_price_range').trigger('change');
                $('#cpsd_category_settings_show_tier_price').trigger('change');
                $('#cpsd_category_settings_switch_price').trigger('change');
                $('#cpsd_category_settings_switch_name').trigger('change');
                $('#cpsd_category_settings_switch_url').trigger('change');
            }
        },1000);

        $('#cpsd_category_settings_enable_cat').on('change', function() {
            if($(this).val() == 1) {
                $('#cpsd_category_settings_show_price_range').trigger('change');
                $('#cpsd_category_settings_show_tier_price').trigger('change');
                $('#cpsd_category_settings_switch_price').trigger('change');
                $('#cpsd_category_settings_switch_name').trigger('change');
                $('#cpsd_category_settings_switch_url').trigger('change');
            }
        });
    });

});
