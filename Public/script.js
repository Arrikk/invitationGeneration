let sides = $(".index-fact__inner-back, .index-fact__inner-front");
let modal = $('#modal')
let imgModal = $('.img-modal')
let imgData;

$(sides).on("click", function (e) {
  let name = $(this).data("name");
  let parent = $(this).parent();
  parent.css({
    border: "2px solid #000000",
    "border-radius": 10,
  });

  $('#sides').val(name)
  $("#modal").show();
  $('.modal-title').text('For '+name)
  console.log(parent);
});
$('.close.v1').click(function(){
  $(modal).hide()
})
$('.close.v2').click(function(){
  $(imgModal).hide()
})

$('#modalForm').on('submit', function(e){
    e.preventDefault();

    $.ajax({
        method: "POST",
        url: "/process",
        data: new FormData(this),
        processData: false,
        contentType: false,
        beforeSend: () => {
          $('#send').attr('disabled', true).text('💞...')
        },
        success: (s) => {
          console.log(s)
          $('#modalImage').attr('src', 'data:image/jpg;base64,'+s.image)
          $(imgModal).fadeIn()
          imgData = s
           $('#send').attr('disabled', false).text('preview')
          },
          error: (e) => {
            alert('Error Occured')
          $('#send').attr('disabled', false).text('preview')

        }
    })
})
$('#confirmBtn').on('click', function(e){
  let btn = $(this)
  console.log(imgData)
    $.ajax({
        method: "POST",
        url: "/save",
        data: imgData,
        beforeSend: () => {
          btn.attr('disabled', true).text('💞....')
        },
        success: (s) => {
          $('#modal-success').show()
          $(imgModal).hide()
          $(modal).hide()
          console.log(s)
        },
        error: (e) => {
          btn.attr('disabled', false).text('Confirm')

        }
    })
})


