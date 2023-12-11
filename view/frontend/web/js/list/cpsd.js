/**
 * Best4Mage Configurable Product Simple Details
 * @author Best4Mage
 */

 define([
    'jquery',
    'mage/translate',
    'mage/template',
    'priceUtils',
    'Magento_Swatches/js/swatch-renderer'
 ], function ($, $t, mageTemplate, priceUtils) {
    'use strict';

    $.widget('cpsd.manageListDetails', $.mage.SwatchRenderer, {

        /**
         * Check if CPSD is enabled.
         *
         * @private
         */
        _isCpsdEnabled: function () {
            return (this.options.isCPSDEnabled);
        },

        /**
         * Event for select
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
             if(!this._isCpsdEnabled()) return this._super($this, $widget);
            this._super($this, $widget);
            this._UpdateProductData($widget);
        },
        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @param {String|undefined} eventName
         * @private
         */
        _OnClick: function ($this, $widget, eventName) {
             if(!this._isCpsdEnabled()) return this._super($this, $widget, eventName);
            this._super($this, $widget, eventName);
            this._UpdateProductData($widget);
        },

        /**
         * Update product data on swatch selection
         *
         * @param {Object} $widget
         * @private
         */
        _UpdateProductData: function ($widget) {
            var containerParent = $widget.element.parents('.product-item-info'),
                productId = $widget.getProduct() ? $widget.getProduct() : 0,
                productsData = $widget.options.jsonProductData;

            if (productsData && productsData[productId]) {
                if (productsData[productId].name) {
                    containerParent.find($widget.options.productNameSelector).html(productsData[productId].name);
                }
                if (productsData[productId].url_suffix) {

                    var urlSelectors = $widget.options.productUrlSelector;
                    $.each(urlSelectors, function(index, selector) {
                        if (containerParent.find(selector).length) {
                            var currentUrl = $(selector).attr('href');
                            var startPart = currentUrl.substr(0, currentUrl.lastIndexOf('/')+1);
                            var newUrl = startPart+productsData[productId].url_suffix;
                            containerParent.find(selector).attr('href',newUrl);
                        }
                    });
                }
            }
            if (productId == 0) {
                var $productPrice = containerParent.find($widget.options.selectorProductPrice);
                $productPrice.removeClass('show');
                $productPrice.find('.price-label').removeClass('hide');
                containerParent.find($widget.options.tierPriceBlockSelector).removeClass('show');
                $productPrice.prev('.price-final_price-range').show();
            }
        },
        
        /**
         * Update total price
         *
         * @private
         */
        _UpdatePrice: function () {

            if(!this._isCpsdEnabled()) return this._super();
            if(this.options.onlySwatches) return this._super();

            var $widget = this,
                $product = $widget.element.parents($widget.options.selectorProduct),
                $productPrice = $product.find(this.options.selectorProductPrice),
                options = _.object(_.keys($widget.optionsMap), {}),
                result,
                tierPriceHtml,
                productId = $widget.getProduct() ? $widget.getProduct() : 0;

            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                var attributeId = $(this).attr('attribute-id');

                options[attributeId] = $(this).attr('option-selected');
            });

            result = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, options)];

            $productPrice.trigger(
                'updatePrice',
                {
                    'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                }
            );
            if(productId != 0) {
                $productPrice.addClass('show');
                $productPrice.find('.price-label').addClass('hide');
                $productPrice.prev('.price-final_price-range').hide();
            }

            if (typeof result != 'undefined' && result.oldPrice.amount !== result.finalPrice.amount) {
                $(this.options.slyOldPriceSelector).show();
            } else {
                $(this.options.slyOldPriceSelector).hide();
            }

            var $tierPriceBox = $product.find(this.options.tierPriceBlockSelector);
            if (typeof result != 'undefined' && result.tierPrices.length) {
                if (this.options.tierPriceTemplate) {
                    tierPriceHtml = mageTemplate(
                        this.options.tierPriceTemplate,
                        {
                            'tierPrices': result.tierPrices,
                            '$t': $t,
                            'currencyFormat': this.options.jsonConfig.currencyFormat,
                            'priceUtils': priceUtils
                        }
                    );
                    $tierPriceBox.addClass('show');
                    $tierPriceBox.find('.tier-price-label').show();
                    $tierPriceBox.find('.content').html(tierPriceHtml);
                }
            } else {
                $tierPriceBox.removeClass('show');
                $tierPriceBox.find('.tier-price-label').hide();
            }

            $(this.options.normalPriceLabelSelector).hide();

            _.each($('.' + this.options.classes.attributeOptionsWrapper), function (attribute) {
                if ($(attribute).find('.' + this.options.classes.optionClass + '.selected').length === 0) {
                    if ($(attribute).find('.' + this.options.classes.selectClass).length > 0) {
                        _.each($(attribute).find('.' + this.options.classes.selectClass), function (dropdown) {
                            if ($(dropdown).val() === '0') {
                                $(this.options.normalPriceLabelSelector).show();
                            }
                        }.bind(this));
                    } else {
                        $(this.options.normalPriceLabelSelector).show();
                    }
                }
            }.bind(this));
        },

    });

    return $.cpsd.manageListDetails;
 });
