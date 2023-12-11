/**
 * Best4Mage Configurable Product Simple Details
 * @author Best4Mage
 */

 define([
 	'jquery',
    'mage/translate',
 	'Magento_ConfigurableProduct/js/configurable'
 ], function ($,$t) {
 	'use strict';

 	$.widget('cpsd.manageDetails', $.mage.configurable, {
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
                attributes: '#additional',
                metainfo:   {
                    title:      'head meta[name="title"]',
                    desc:       'head meta[name="description"]',
                    keywords:   'head meta[name="keywords"]'
                }
            }
 		},

        _isCpsdEnabled: function ($widget) {
            return ($widget.options.cpsdConfig.enable && $widget.options.cpsdConfig.labels.length > 0);
        },
        _create: function () {
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
            this._PreselectOptions($widget);
        },
 		/**
         * Configure an option, initializing it's state and enabling related options, which
         * populates the related option's selection and resets child option selections.
         * @private
         * @param {*} element - The element associated with a configurable option.
         */
        _configureElement: function (element) {
        	this._super(element);
            this._UpdateProductData();
        },

        _UpdateProductData: function () {
            var $widget = this,
                productId = $widget.simpleProduct,
                productsData = $widget.options.cpsdProducts,
                defaultSelectors = $widget.options.defaultSelectors,
                cpsdConfig = $widget.options.cpsdConfig;

            if ($widget._isCpsdEnabled($widget) && typeof productId != 'undefined') {
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
                                cpsdConfig.selectors.short_desc_selector ? cpsdConfig.selectors.short_desc_selector : defaultSelectors.sdesc
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
                        case 'meta-info':
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

        _UpdateDetail: function (cdata, pdata, selector) {
            var defaultSelectors = this.options.defaultSelectors;
            var configSelectors = this.options.cpsdConfig.selectors;
            if (selector == 'url') {
                if (cdata != '' && typeof window.history != 'undefined') {
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
                            '<meta name="keywords" content="' + cdata['meta_keyword'] + '" />'
                        );
                    }
                } else {
                    if (pdata.keyword != '' && pdata.keyword != null) {
                        if ($(defaultSelectors.metainfo.keywords).length) {
                            $(defaultSelectors.metainfo.keywords).attr('content', pdata.keyword);
                        } else {
                            $(defaultSelectors.metainfo.desc).after(
                                '<meta name="keywords" content="' + pdata['meta_keyword'] + '" />'
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
					$(selector).html(pdata);
                    console.log('Best4Mage_CPSD::"'+selector+'" not found.');
                }
            }
        },

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
                        $.each(options.spConfig.attributes, function (aid,attr) {
                            if(attr.code == attrCode) {
                                var attrId = aid;
                                var opId = null;
                                $.each(attr.options, function (key,opt) {
                                    if(opt.label == attrVal) {
                                        opId = opt.id;
                                    }
                                });
                                if(opId != null) {
                                    $('#attribute'+aid).val(opId).trigger('change');
                                }
                            }
                        });
                    }
                });
            } else {
                var preselectType = options.cpsdConfig.preselect_type;
                if (preselectType == 4) {
                    var opId = null;
                    $.each(options.spConfig.attributes, function (aid,attr) {
                        $.each(attr.options, function (key,opt) {
                            if(key == 0 && opt.id != '') {
                                opId = opt.id;
                            } else if(key == 1) {
                                opId = opt.id;
                            }
                        });
                        if(opId != null) {
                            $('#attribute'+aid).val(opId).trigger('change');
                        }
                    });
                } else {
                    var preselect = options.cpsdProducts.preselect;
                    console.log(preselect);
                    if(preselect != 0) {
                        var spConfig = options.cpsdProducts[preselect].spConfig; console.log(spConfig);
                        $.each(spConfig, function(i,v) {
                            $('#attribute'+i).val(v).trigger('change');
                        });
                    }
                }
            }
        }
 	});

 	return $.cpsd.manageDetails;
 });
