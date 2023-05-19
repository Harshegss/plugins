(function ($) {
  $(document).ready(function () {
    $("#ocl_send_mail").on("click", function () {
      let data = {
        action: "ocl_send_email",
        data: $(this).siblings("input").val(),
      };

      $.ajax({
        url: ajaxurl,
        type: "POST",
        data: data,
        success: function (response) {
          console.log(response);
        },
        error: function (errorThrown) {
          console.error(errorThrown);
        },
      });
    });
  });
})(jQuery);
