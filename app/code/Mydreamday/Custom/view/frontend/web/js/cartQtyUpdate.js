define([
  'jquery',
  'Magento_Checkout/js/action/get-totals',
  'Magento_Customer/js/customer-data',
  'Magento_Ui/js/modal/modal',
  'lodash',
  'jquery/ui'
], function ($, getTotalsAction, customerData, modal) {

  $(document).ready(function () {
    // Create the debounced function outside of the event listener
    var debouncedAjaxCall = _.debounce(function ($element) {
      var form = $('form#form-validate');
      $.ajax({
        url: form.attr('action'),
        data: form.serialize(),
        showLoader: true,
        success: function (res) {
          if (res.status != 0) {
            var parsedResponse = $.parseHTML(res);
            var result = $(parsedResponse).find("#form-validate");
            var sections = ['cart'];
            $("#form-validate").replaceWith(result);
            customerData.reload(sections, true);
            //getTotalsAction([], deferred);
          } else {
            $element.val(parseInt(res.maxQty));
            $.alert({
              content: res.msg
            });
          }
          $element.trigger('change');
        },
        error: function (xhr, status, error) {
          var err = eval("(" + xhr.responseText + ")");
          console.log(err.Message);
        }
      });
    }, 500);

    $(document).on('change', '.cart.item .input-text.qty', function () {
      var $this = $(this);

      // Call the debounced function
      debouncedAjaxCall($this);
    });


    $(document).on('click', '.cart.item .qty-minus', function (e) {
      e.preventDefault();
      var input = $(this).parent().find("input");
      input.data('val', input.val());
      input.val(parseInt(input.val()) - 1 >= 0 ? parseInt(input.val()) - 1 : 0);

      input.trigger('change');
    });

    $(document).on('click', '.cart.item .qty-plus', function (e) {
      e.preventDefault();
      var input = $(this).parent().find("input");
      input.data('val', input.val());
      input.val(parseInt(input.val()) + 1);
      input.trigger('change');
    });

    $(document).on('click', '.img-wrap .action-delete-btn', function () {
      var input = $(this).parents('.item-info').find('input[name$="[qty]"]');
      input.val(0);
      input.trigger('change');
    });
  });
});
