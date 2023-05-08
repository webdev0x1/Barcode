define(['uiElement','ko','mage/storage', 'onscan', 'jquery'], function(Element, ko, storage, scan, $){
    //needs to return a js object extended from uiElement that
    //defines a template.  Magento and Knockout.js will use
    //the returned object as a view model constructor
    var allowedInput = function() {
        return {
          init: function(element, valueAccessor, allBindingsAccessor, bindingContext) {
            ko.bindingHandlers.textInput.init(element, valueAccessor, allBindingsAccessor, bindingContext);
          },
      
          update: function(element, valueAccessor) {
            var value = ko.unwrap(valueAccessor());
            if(value && value != undefined) {
              if(value != '\n')
              if (isNaN(value)) {
                valueAccessor()(value.slice(0, -1));
              }
            }
          }
        }
      };
      
    ko.bindingHandlers.numericInput = allowedInput();
    viewModelConstructor = Element.extend({
        defaults: {
            template: 'Akwaaba_Barcode/akwaaba_inventory'
        },
        message: ko.observable(""),
        isSelected: ko.observable(true),
        barcode: ko.observable(),
        product:ko.observable(""),
        sellerid: '',
        inventoryFocus: function () { console.log('selected'); this.isSelected(true); },
        initialize: function (config) {
            // Enable scan events for the entire document
            this.sellerid = config.sellerid;
            // Initialize with options
            
            scan.attachTo(document, {
            suffixKeyCodes: [13], // enter-key expected at the end of a scan
            reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
              onScan: function(sCode, iQty) { // Alternative to document.addEventListener('scan')
                  //console.log('Scanned: ' + iQty + 'x ' + sCode); 
                    //console.log('Pressed: ' + iKeyCode);
                  // Simulate a scan programmatically - e.g. to test event handlers
                  $('#barcode-inventory').trigger('processStart');
                  // scan.simulate(document, '333323');
                  // scan.simulate(document, '3333234');
                  barcodes = [];
                  barcodes[0] = sCode;
                  // barcodes[1] = '3333234';
                  data = [];
                  console.log(this.sellerid);
                  $('#seller_id').val(config.sellerid);
                  data['seller_id'] = $('#seller_id').val();
                  data['inventory'] = $('#inventory').val();
                  data['barcodes'] = barcodes;
                  var product_qty = $("input[name='product_qty[]']").map(function(){return $(this).val();}).get();
                  var product_id = $("input[name='product_id[]']").map(function(){return $(this).val();}).get();
                  data['product_qty'] = product_qty;
                  data['product_id'] = product_id;
                  var i =0;
                  qty_product = [];
                  $.each(data['product_id'], function(index, value) {
                    // console.log('ID'+value);
                    // console.log('QTY'+data['product_qty'][i]);
                    qty_product[value.toString()] = $("#product-qty-"+value).val();// data['product_qty'][i];
                    i++;
                  });
                  var arr = [];
                  //console.log(JSON.stringify(qty_product));
                   var filtered = qty_product.filter(function (el, index) {
                    if(el != null) {
                      arr.push({id: index, qty: $("#product-qty-"+index).val()})
                    }
                   });
                   qty_product = arr;
                  //alert(JSON.stringify(qty_product));
                  payload = {
                    seller_id: data['seller_id'],
                    barcodes: barcodes,
                    inventory: data['inventory'],
                    product_qty: arr,
                    form_key: window.FORM_KEY
                  };
                  storage.post(
                    'product',
                    JSON.stringify(payload),
                    true
                ).done(function (data) {
                  $('#barcode-inventory').trigger('processStop');
                  if(data.status == 'success') {
                    console.log(data);
                    var html = '';
                    $.each(data.product_details, function (index, value) {
                      if($("#"+index).length) {
                        $("#product-qty-"+index).val(value.product_qty);
                        $("#qty-"+index).html(value.product_qty);
                      } else {
                        html += '<tr id="'+index+'">';
                        html += '<td class="col-sm-3"><img width="180" src="'+value.barcode_image_url+'" /></td>';
                        html += '<td>'+value.product_name+'</td>';
                        html += '<td><img class="col-sm-3" width="180" src="'+value.product_image_url+'" /></td>';
                        html += '<td><span id="qty-'+index+'">'+value.product_qty+'</span><input type="hidden" value="'+index+'" name="product_id[]" id="product-id-'+index+'"/><input type="hidden" value="'+value.product_qty+'" name="product_qty[]" id="product-qty-'+index+'"/></td>';
                        html += '<td>'+value.product_price+'</td>';
                        html += '<td><a href="javascript:void(0)" onclick="$(this).closest(';
                        html += "'tr'";
                        html += ').remove();">Remove</a></td>';
                        html += '</tr>';
                      }
                    });
                    $('#barcode-products').append(html);
                  } else {
                    alert('Error while processing your request!');
                  }

                  $('#barcode-inventory').trigger('processStop');
                });
              },
              onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
                
            }
          });
          //scan.detachFrom(document);

            this._super();

            _.bindAll(this, 'inventoryFocus', 'barcodeProduct');
            
            return this;
        },

        initObservable: function () {
            this._super();

            this.isSelected = ko.observable(true);

            return this;
        },
        barcodeProduct: function(data,event) {
           
        },
        inventory: function () {
                  barcodes[0] = '333323';
                  barcodes[1] = '3333234';
                  
                  data['seller_id'] = $('#seller_id').val();
                  data['inventory'] = $('#inventory').val();
                  data['barcodes'] = barcodes;
                  var product_qty = $("input[name='product_qty[]']").map(function(){return $(this).val();}).get();
                  var product_id = $("input[name='product_id[]']").map(function(){return $(this).val();}).get();
                  data['product_qty'] = product_qty;
                  data['product_id'] = product_id;
                  var i =0;
                  qty_product = [];
                  $.each(data['product_id'], function(index, value) {
                    // console.log('ID'+value);
                    // console.log('QTY'+data['product_qty'][i]);
                    qty_product[value.toString()] = $("#product-qty-"+value).val();// data['product_qty'][i];
                    i++;
                  });
                  var arr = [];
                  //console.log(JSON.stringify(qty_product));
                   var filtered = qty_product.filter(function (el, index) {
                    if(el != null) {
                      arr.push({id: index, qty: $("#product-qty-"+index).val()})
                    }
                   });
                   qty_product = arr;
                   payload = {
                    seller_id: data['seller_id'],
                    barcodes: barcodes,
                    inventory: data['inventory'],
                    product_qty: arr,
                    form_key: window.FORM_KEY
                  };

            storage.post(
              'inventory',
              JSON.stringify(payload),
              true
            ).done(function (data) {
              if(data.status == 'success') {
                alert('Inventory successfull!');
                $('#barcode-products').html('');
              } else {
                console.log('Error while processing your request!');
              }

            });
        }
    });
    return viewModelConstructor;
});