$(function () {
  "use strict";

  // Feather Icon Init Js
  // feather.replace();

  // $(".preloader").fadeOut();

  // =================================
  // Tooltip
  // =================================
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[title]:not([dontTooltip])')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // =================================
  // Popover
  // =================================
  var popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]')
  );
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });

  

  // increment & decrement
  $(".minus,.add").on("click", function () {
    var $qty = $(this).closest("div").find(".qty"),
      currentVal = parseInt($qty.val()),
      isAdd = $(this).hasClass("add");
    !isNaN(currentVal) &&
      $qty.val(
        isAdd ? ++currentVal : currentVal > 0 ? --currentVal : currentVal
      );
  });

  // fixed header
  $(window).scroll(function () {
    if ($(window).scrollTop() >= 60) {
      $(".app-header").addClass("fixed-header");
    } else {
      $(".app-header").removeClass("fixed-header");
    }
  });

  // Checkout
  $(function () {
    $(".billing-address").click(function () {
      $(".billing-address-content").hide();
    });
    $(".billing-address").click(function () {
      $(".payment-method-list").show();
    });
  });
});

/*change layout boxed/full */
$(".full-width").click(function () {
  $(".container-fluid").addClass("mw-100");
  $(".full-width i").addClass("text-primary");
  $(".boxed-width i").removeClass("text-primary");
});
$(".boxed-width").click(function () {
  $(".container-fluid").removeClass("mw-100");
  $(".full-width i").removeClass("text-primary");
  $(".boxed-width i").addClass("text-primary");
});

/*Dark/Light theme*/
$(".light-logo").hide();
$(".dark-theme").click(function () {
  $("nav.navbar-light").addClass("navbar-dark");
  $(".dark-theme i").addClass("text-primary");
  $(".light-theme i").removeClass("text-primary");
  $(".light-logo").show();
  $(".dark-logo").hide();
});
$(".light-theme").click(function () {
  $("nav.navbar-light").removeClass("navbar-dark");
  $(".dark-theme i").removeClass("text-primary");
  $(".light-theme i").addClass("text-primary");
  $(".light-logo").hide();
  $(".dark-logo").show();
});

/*Card border/shadow*/
$(".cardborder").click(function () {
  $("body").addClass("cardwithborder");
  $(".cardshadow i").addClass("text-dark");
  $(".cardborder i").addClass("text-primary");
});
$(".cardshadow").click(function () {
  $("body").removeClass("cardwithborder");
  $(".cardborder i").removeClass("text-primary");
  $(".cardshadow i").removeClass("text-dark");
});

$(".change-colors li a").click(function () {
  $(".change-colors li a").removeClass("active-theme");
  $(this).addClass("active-theme");
});

/*Theme color change*/
function toggleTheme(value) {
  $(".preloader").show();
  var sheets = document.getElementById("themeColors");
  sheets.href = value;
  $(".preloader").fadeOut();
}
$(".preloader").fadeOut();

$('form').on("submit", function(e){
  //e.preventDefault()
  var $button = $(this).find("button[type=submit]");
  $button.attr("disabled", "");
  $button.find('span').removeClass("d-none")
});
$('input, select').on('focus', function(){
  var $this = $(this);
  $this.siblings('div.form-error').fadeOut(1000)
})
if($(".toastify").length){
  $(".toastify").each(function(index){
    toast($(this).attr("data-title"))
  })
}

function toast(message){
  Toastify({
    text: message,
    duration: 10000,
    close: true,
    gravity: "top", // `top` or `bottom`
    position: "right", // `left`, `center` or `right`
    stopOnFocus: true, // Prevents dismissing of toast on hover
    style: {
      background: "linear-gradient(to right, #00b09b, #5D87FF)",
    },
  }).showToast();
};

$('.image-upload-trigger-btn').on('click', function(e){
  e.preventDefault()
  //$(this).parents('.picture-upload-container').find('input.image-uploader').click()
  $('input.image-uploader').trigger('click')
})

$('input.image-uploader').on('change', function(e){
  if(!e){
    return
  }
  var $this = $(this)
  if (!String($this.attr('accept')).includes(("." + e?.target?.files[0].name.split('.').pop()))){
    toast('Please upload a valid image')
    return;
  }
  const maxFileSize = parseInt($this.attr('data-maxsize'))
  if (e?.target?.files[0].size > maxFileSize) {
    toast('The uploaded file must not exceed ' +(maxFileSize / (1024 * 1024))+' Mb' );
    return;
  }
  $parentForm = $(this).parents('form')
  $parentForm.find('img').attr("src", URL.createObjectURL(e?.target?.files[0]))
  var immediateUploadBtn = $parentForm.find('.immediate-upload-btn')
  if(!!immediateUploadBtn){
    immediateUploadBtn.removeAttr("disabled")
  }
})

if($(".select2").length){
  $(".select2").select2({
    placeholder: "Select...",
    //allowClear: true
  });
}


if($(".sampleTable").length){
  $(".sampleTable").fancyTable({
    /* Column number for initial sorting*/
     //sortColumn:2,
     /* Setting pagination or enabling */
     pagination: true,
     /* Rows per page kept for display */
     perPage:4,
     globalSearch:true,
     globalSearchExcludeColumns:[1, 2, 3],
     paginationClass: 'page-link link',
     //paginationElement:"#myPaginator"
     });
}   
$('[data-bs-toggle="my-tab"]').on('click', function(e){
  e.preventDefault();
  var $this = $(this)
  var targetId = $(this).attr('data-href');
  var allTabePanes = $this.parents('body').find(".tab-pane")
  var allTRs = $this.parents('body').find("tr")
  allTabePanes.removeClass('active')
  allTabePanes.removeClass('show')
  allTRs.removeClass('is-active')
  allTRs.removeClass('bg-light-info')
  $this.parents('body').find("#"+targetId+".tab-pane").addClass('active show')
  $this.parent('td').parent('tr').addClass('is-active bg-light-info')
})