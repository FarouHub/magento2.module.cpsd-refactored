/**
 * Best4Mage Configurable Product Simple Details
 * @author Best4Mage
 */

 define([
    'jquery',
    'mage/translate',
    'Magento_Swatches/js/swatch-renderer'
 ], function ($, $t) {
    'use strict';

    $.widget('cpsd.manageDetails', $.mage.SwatchRenderer, {
        options: {
            defaultSelectors: {
                name:           '.page-title .base',
                sku:            '.product.attribute.sku .value',
                desc:           '#description',
                sdesc:          '.product.attribute.overview',
                stock:          '.stock span',
                qty:            '#qty',
                qtyIncNotice:   '.product.pricing',
                cartBtnWrap:    '.box-tocart .actions',
                qtyWrap:        '.field.qty',
                attributes:     '#additional',
                metainfo:       {
                    title:      'head meta[name="title"]',
                    desc:       'head meta[name="description"]',
                    keywords:   'head meta[name="keywords"]'
                }
            }
        },

        /**
         * Check if CPSD is enabled.
         *
         * @private
         */
        _isCpsdEnabled: function ($widget) {
            return ($widget.options.cpsdConfig.enable && $widget.options.cpsdConfig.labels.length > 0);
        },

        /**
         * Render controls
         *
         * @private
         */
        _RenderControls: function () {
            if (!this._isCpsdEnabled(this)) return this._super();

            var $widget = this,
                productsData = $widget.options.cpsdProducts,
                defaultSelectors = $widget.options.defaultSelectors,
                cpsdConfig = $widget.options.cpsdConfig,
                isMetaEnabled = false;

            for (var i = cpsdConfig.labels.length - 1; i >= 0; i--) {
                if (cpsdConfig.labels[i] == 'meta-info') {
                    isMetaEnabled = true;
                    break;
                }
            }
            if (isMetaEnabled) {
                productsData[0].metainfo.title = (productsData[0].metainfo.title == null) ? document.title : productsData[0].metainfo.title;

                productsData[0].metainfo.desc = (productsData[0].metainfo.desc == null) ? $(defaultSelectors.metainfo.desc).attr('content') : productsData[0].metainfo.desc;

                if ($(defaultSelectors.metainfo.keywords).length > 0 && productsData[0].metainfo.desc.keyword == null) {
                    productsData[0].metainfo.keyword = $(defaultSelectors.metainfo.keywords).attr('content');
                }
            }

            this._super();
            this.options.tierPriceTemplate = $(this.options.tierPriceTemplateSelector).html();
            this._PreselectOptions($widget);
        },

        /**
         * Event for select
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
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
            var productId = $widget.getProduct() ? $widget.getProduct() : 0,
                productsData = $widget.options.cpsdProducts,
                defaultSelectors = $widget.options.defaultSelectors,
                cpsdConfig = $widget.options.cpsdConfig;

            if ($widget._isCpsdEnabled($widget)) {
                $(cpsdConfig.labels).each(function(index, label) {                
                    switch (label) {
                        case 'history':
                            $widget._UpdateDetail(
                                productsData[productId].url_suffix,
                                productsData[0].url_suffix,
                                'url'
                            );
                            break;
                        case 'name':
                            $widget._UpdateDetail(
                                productsData[productId].name,
                                productsData[0].name,
                                cpsdConfig.selectors.name_selector ? cpsdConfig.selectors.name_selector : defaultSelectors.name
                            );
                            break;
                        case 'sku':
                            $widget._UpdateDetail(
                                productsData[productId].sku,
                                productsData[0].sku,
                                cpsdConfig.selectors.sku_selector ? cpsdConfig.selectors.sku_selector : defaultSelectors.sku
                            );
                            break;
                        case 'stock':
                            $widget._UpdateDetail(
                                productsData[productId].stock.is_in_stock ? productsData[productId].stock.instock_label : productsData[productId].stock.outofstock_label,
                                productsData[0].stock.is_in_stock ? productsData[0].stock.instock_label : productsData[0].stock.outofstock_label,
                                cpsdConfig.selectors.stock_selector ? cpsdConfig.selectors.stock_selector : defaultSelectors.stock
                            );
                            break;
                        case 'min_max_qty':
                            $widget._UpdateDetail(
                                {min:productsData[productId].stock.min_sale_qty, max:productsData[productId].stock.max_sale_qty},
                                {min:productsData[0].stock.min_sale_qty, max:productsData[0].stock.max_sale_qty},
                                'min_max_qty'
                            );
                            break;
                        case 'qty_increments':
                            var cInc = productsData[productId].stock.qty_increment;
                            var pInc = productsData[0].stock.qty_increment;
                            var cMinQty = productsData[productId].stock.min_sale_qty ? ((productsData[productId].stock.min_sale_qty < cInc) ? cInc : productsData[productId].stock.min_sale_qty ) : cInc;
                            var pMinQty = productsData[0].stock.min_sale_qty ? ((productsData[0].stock.min_sale_qty < pInc) ? pInc : productsData[0].stock.min_sale_qty) : pInc;
                            $widget._UpdateDetail(
                                [cInc,productsData[productId].name,cMinQty],
                                [pInc,productsData[0].name,pMinQty],
                                'qty_increments'
                            );
                            break;
                        case 'short_description':
                            $widget._UpdateDetail(
                                productsData[productId].sdesc,
                                productsData[0].sdesc,
                                cpsdConfig.selectors.short_desc_selector ? cpsdConfig.selectors.short_desc_selector : defaultSelectors.sdesc,
                                true
                            );
                            break;
                        case 'description':
                            $widget._UpdateDetail(
                                productsData[productId].desc,
                                productsData[0].desc,
                                cpsdConfig.selectors.desc_selector ? cpsdConfig.selectors.desc_selector : defaultSelectors.desc
                            );
                            break;
                        case 'attributes':
                            $widget._UpdateDetail(
                                productsData[productId].attributes,
                                productsData[0].attributes,
                                cpsdConfig.selectors.more_info_selector ? cpsdConfig.selectors.more_info_selector : defaultSelectors.attributes
                            );
                            break;
                        case 'meta_info':
                            $widget._UpdateDetail(
                                productsData[productId].metainfo,
                                productsData[0].metainfo,
                                'meta-info'
                            );
                            break;
                    }
                });
            }
        },

        /**
         * Update product details
         *
         * @param {Object} cdata
         * @param {Object} pdata
         * @param {String} selector
         * @param {Boolean} isSdesc
         * @private
         */
        _UpdateDetail: function (cdata, pdata, selector, isSdesc) {
            if(isSdesc === undefined) isSdesc=false;
            var defaultSelectors = this.options.defaultSelectors;
            var configSelectors = this.options.cpsdConfig.selectors;
            if (selector == 'url') {
                if (cdata != '' && cdata != null && typeof window.history != 'undefined') {
                    var currentUrl = window.location.pathname;
                    var startPart = currentUrl.substr(0, currentUrl.lastIndexOf('/')+1);
                    window.history.replaceState(null, 'CPSD', startPart+cdata);
                } else if (pdata != '' && typeof window.history != 'undefined') {
                    window.history.replaceState(null, 'CPSD', pdata);
                }
            } else if (selector == 'meta-info') {
                if (cdata.desc != '' && cdata.desc != null) {
                    $(defaultSelectors.metainfo.desc).attr('content', cdata.desc);
                } else {
                    $(defaultSelectors.metainfo.desc).attr('content', pdata.desc);
                }
                if (cdata.keyword != '' && cdata.keyword != null) {
                    if ($(defaultSelectors.metainfo.keywords).length) {
                        $(defaultSelectors.metainfo.keywords).attr('content', cdata.keyword);
                    } else {
                        $(defaultSelectors.metainfo.desc).after(
                            '<meta name="keywords" content="' + cdata.keyword + '" />'
                        );
                    }
                } else {
                    if (pdata.keyword != '' && pdata.keyword != null) {
                        if ($(defaultSelectors.metainfo.keywords).length) {
                            $(defaultSelectors.metainfo.keywords).attr('content', pdata.keyword);
                        } else {
                            $(defaultSelectors.metainfo.desc).after(
                                '<meta name="keywords" content="' + pdata.keyword + '" />'
                            );
                        }
                    } else {
                        $(defaultSelectors.metainfo.keywords).remove();
                    }
                }
                if (cdata.title != '' && cdata.title != null) {
                    document.title =  cdata.title;
                } else {
                    document.title =  pdata.title;
                }
            } else if (selector == 'min_max_qty') {
                var qtySelector = configSelectors.qty_selector ? configSelectors.qty_selector : defaultSelectors.qty;
                if($(qtySelector).length && $(qtySelector).attr('data-validate')) {
                    var minQty = 1;
                    var qtyValidateData = JSON.parse($(qtySelector).attr('data-validate'));
                    if(qtyValidateData['validate-item-quantity']) {
                        if(cdata.min || cdata.max) {
                            cdata.min 
                                ? qtyValidateData['validate-item-quantity']['minAllowed'] = cdata.min
                                : delete qtyValidateData['validate-item-quantity']['minAllowed'];
                            cdata.max 
                                ? qtyValidateData['validate-item-quantity']['maxAllowed'] = cdata.max
                                : delete qtyValidateData['validate-item-quantity']['maxAllowed'];
                        } else if(pdata.min || pdata.max) {
                            pdata.min 
                                ? qtyValidateData['validate-item-quantity']['minAllowed'] = pdata.min
                                : delete qtyValidateData['validate-item-quantity']['minAllowed'];
                            pdata.max 
                                ? qtyValidateData['validate-item-quantity']['maxAllowed'] = pdata.max
                                : delete qtyValidateData['validate-item-quantity']['maxAllowed'];
                        } else {
                            delete qtyValidateData['validate-item-quantity'];
                        }
                        minQty = qtyValidateData['validate-item-quantity']['minAllowed'];
                    } else {
                        qtyValidateData['validate-item-quantity'] = [];
                        if(cdata.min || cdata.max) {
                            cdata.min 
                                ? qtyValidateData['validate-item-quantity']['minAllowed'] = cdata.min
                                : delete qtyValidateData['validate-item-quantity']['minAllowed'];
                            cdata.max 
                                ? qtyValidateData['validate-item-quantity']['maxAllowed'] = cdata.max
                                : delete qtyValidateData['validate-item-quantity']['maxAllowed'];
                        } else if(pdata.min || pdata.max) {
                            pdata.min 
                                ? qtyValidateData['validate-item-quantity']['minAllowed'] = pdata.min
                                : delete qtyValidateData['validate-item-quantity']['minAllowed'];
                            pdata.max 
                                ? qtyValidateData['validate-item-quantity']['maxAllowed'] = pdata.max
                                : delete qtyValidateData['validate-item-quantity']['maxAllowed'];
                        } else {
                            delete qtyValidateData['validate-item-quantity'];
                        }
                        minQty = qtyValidateData['validate-item-quantity']['minAllowed'];
                    }
                    $(qtySelector).val(minQty).trigger('change');
                    $(qtySelector).attr('data-validate', JSON.stringify(qtyValidateData));
                }
            } else if (selector == 'qty_increments') {
                var qtyPos = this.options.cpsdConfig.qty_inc_pos;
                var qtySelector = configSelectors.qty_selector ? configSelectors.qty_selector : defaultSelectors.qty;
                if($(qtySelector).length && $(qtySelector).attr('data-validate')) {
                    var qtyValidateData = JSON.parse($(qtySelector).attr('data-validate'));
                    if(qtyValidateData['validate-item-quantity']) {
                        if(cdata[0]) {
                            qtyValidateData['validate-item-quantity']['qtyIncrements'] = cdata[0];
                            $(qtySelector).val(cdata[2]).trigger('change');
                        } else if(pdata[0]) {
                            qtyValidateData['validate-item-quantity']['qtyIncrements'] = pdata[0];
                            $(qtySelector).val(pdata[2]).trigger('change');
                        } else {
                            delete qtyValidateData['validate-item-quantity']['qtyIncrements'];
                        }
                    } else {
                        qtyValidateData['validate-item-quantity'] = [];
                        if(cdata[0]) {
                            qtyValidateData['validate-item-quantity']['qtyIncrements'] = cdata[0];
                            $(qtySelector).val(cdata[2]).trigger('change');
                        } else if(pdata[0]) {
                            qtyValidateData['validate-item-quantity']['qtyIncrements'] = pdata[0];
                            $(qtySelector).val(pdata[2]).trigger('change');
                        } else {
                            delete qtyValidateData['validate-item-quantity']['qtyIncrements'];
                        }
                    }
                    $(qtySelector).attr('data-validate', JSON.stringify(qtyValidateData));

                    $(defaultSelectors.qtyIncNotice).remove();
                    var noticeHtml = '<div class="product pricing" style="margin:10px 0;">';
                    var notice = $t('%1 is available to buy in increments of %2');
                    if(cdata[0]) {
                        notice = notice.replace('%1',cdata[1]).replace('%2',cdata[0]);
                        noticeHtml += notice;
                    } else if(pdata[0]) {
                        notice = notice.replace('%1',pdata[1]).replace('%2',pdata[0]);
                        noticeHtml += notice;
                    }
                    noticeHtml += '</div>';
                    if (qtyPos == 0) {
                        $('.product-info-main').append(noticeHtml);
                    } else if (qtyPos == 1) {
                        var pos1Selector = configSelectors.actions_selector ? configSelectors.actions_selector : defaultSelectors.cartBtnWrap;
                        $(pos1Selector).prepend(noticeHtml);
                    } else if (qtyPos == 2) {
                        var pos2Selector = configSelectors.qty_wrap_selector ? configSelectors.qty_wrap_selector : defaultSelectors.qtyWrap;
                        $(pos2Selector).prepend(noticeHtml);
                    } else if (qtyPos == 3) {
                        var pos3Selector = configSelectors.name_selector ? configSelectors.name_selector : defaultSelectors.name;
                        $(pos3Selector).closest('div').append(noticeHtml);
                    }
                }
            } else {
                if ($(selector).length) {
                    $(selector).html(cdata);
                } else {
                    if(isSdesc) {
                        var sDescHtml = '<div class="product attribute overview"><div class="value" itemprop="description">'
                            + cdata
                            + '</div></div>';
                        $('.product-info-main').append(sDescHtml);
                    }
                    console.log('Best4Mage_CPSD::"'+selector+'" not found.');
                }
            }
        },

        /**
         * Preselect options on load.
         *
         * @param {Object} $widget
         * @private
         */
        _PreselectOptions: function ($widget) {
            var options = $widget.options,
                currentPath = window.location.pathname,
                selectedOptions = [];

            currentPath = currentPath.substr(currentPath.lastIndexOf('/')+1);
            currentPath = currentPath.substr(0,currentPath.lastIndexOf('.html'));
            selectedOptions = currentPath.split('+');
            selectedOptions.shift();

            if (selectedOptions.length > 0) {
                $(selectedOptions).each(function(index,op) {
                    if (typeof op === 'string') {
                        var attrCode = op.substr(0,op.indexOf('-'));
                        var attrVal = op.substr(op.indexOf('-')+1);
                        if (attrVal.indexOf('~')) {
                            attrVal = attrVal.replace('~',' ');
                        }
                        try {
                            if ($('[attribute-code="'+attrCode+'"] .swatch-attribute-options').children().is('div')) {
                                $('[attribute-code="'+attrCode+'"] .swatch-option[option-label="'+attrVal+'"]').trigger('click');
                            } else {
                                $.each($('[attribute-code="'+attrCode+'"] .swatch-attribute-options select option'), function(i,v) {
                                    if (v.text==attrVal) {
                                        $('[attribute-code="'+attrCode+'"] .swatch-attribute-options select').val(v.value).trigger('change');
                                        return;
                                    }
                                });
                            }
                        } catch (e) {
                            console.log($.mage.__('Issue with auto selection of options by url'));
                        }
                    }
                });
            } else {
                var preselectType = options.cpsdConfig.preselect_type;
                if (preselectType == 4) {
                    $('.swatch-attribute').each(function(i,v) {
                        $(this).find('.swatch-option').first().trigger('click');
                    });
                } else {
                    var preselect = options.cpsdProducts.preselect;
                    console.log(preselect);
                    if(preselect != 0) {
                        var spConfig = options.cpsdProducts[preselect].spConfig; console.log(spConfig);
                        $.each(spConfig, function(i,v) {
                            try {
                                if ($('.swatch-attribute[attribute-id='+i+'] .swatch-attribute-options').children().is('div')) {
                                    $('.swatch-attribute[attribute-id='+i+'] .swatch-attribute-options [option-id='+v+']').trigger('click');
                                } else {
                                    $('.swatch-attribute[attribute-id='+i+'] .swatch-attribute-options select').val(v).trigger('change');
                                }
                            } catch (e) {
                                console.log($.mage.__('Issue with auto selection of options by predefined config.'));
                            }
                        });
                    }
                }
            }
        },

        /**
         * Start update base image process based on event name
         *
         * @param {Array} images
         * @param {jQuery} context
         * @param {Boolean} isInProductView
         * @param {String|undefined} eventName
         */
        updateBaseImage: function (images, context, isInProductView, eventName) {
            if(eventName === undefined) eventName=null;
            if (!this._isCpsdEnabled(this)) return this._super(images, context, isInProductView, eventName);
            var gallery = context.find(this.options.mediaGallerySelector).data('gallery');

            if ((eventName === undefined || eventName === null) && typeof gallery != 'undefined') {
                var justAnImage = images[0],
                    initialImages = this.options.mediaGalleryInitial,
                    imagesToUpdate,
                    isInitial;

                if (isInProductView) {
                    imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                    isInitial = _.isEqual(imagesToUpdate, initialImages);

                    if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                        imagesToUpdate = imagesToUpdate.concat(initialImages);
                    }

                    imagesToUpdate = this._setImageIndex(imagesToUpdate);
                    gallery.updateData(imagesToUpdate);

                    if (isInitial) {
                        $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
                    } else {
                        $(this.options.mediaGallerySelector).AddFotoramaVideoEvents({
                            selectedOption: this.getProduct(),
                            dataMergeStrategy: this.options.gallerySwitchStrategy
                        });
                    }

                    gallery.first();

                } else if (justAnImage && justAnImage.img) {
                    context.find('.product-image-photo').attr('src', justAnImage.img);
                }
            } else {

                context.find(this.options.mediaGallerySelector).on('gallery:loaded', function (loadedGallery) {
                    loadedGallery = context.find(this.options.mediaGallerySelector).data('gallery');
                    var justAnImage = images[0],
                        initialImages = this.options.mediaGalleryInitial,
                        imagesToUpdate,
                        isInitial;

                    if (isInProductView) {
                        imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                        isInitial = _.isEqual(imagesToUpdate, initialImages);

                        if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                            imagesToUpdate = imagesToUpdate.concat(initialImages);
                        }

                        imagesToUpdate = this._setImageIndex(imagesToUpdate);
                        loadedGallery.updateData(imagesToUpdate);

                        if (isInitial) {
                            $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
                        } else {
                            $(this.options.mediaGallerySelector).AddFotoramaVideoEvents({
                                selectedOption: this.getProduct(),
                                dataMergeStrategy: this.options.gallerySwitchStrategy
                            });
                        }

                        loadedGallery.first();

                    } else if (justAnImage && justAnImage.img) {
                        context.find('.product-image-photo').attr('src', justAnImage.img);
                    }
                }.bind(this));
            }
        },
    });

    return $.cpsd.manageDetails;
 });
