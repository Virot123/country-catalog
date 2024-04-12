$('.vr-head-form-sort select').on('change',function(){
    $('.vr-navbar-btn-search').click();
});
if($('.btn_popup').length){
    $('.btn_popup').on('click',function(){
        let data ={
            name:$(this).text()
      }
        $.ajax({
            type: "GET" ,
            url: "/country-catalog/"+data.name,
            data: data,
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            beforeSend: (xhr) => {
            },
            success: (data) => {
              console.log(data)
              if(data['status']==true){
                let str='<div class="vr_popup vr-popup"></div>';
                $('body').append(str);
                $('.vr_popup').html(data['result']);
                $('.vr_popup').css('display','flex');
                afterPopup();
              }else{
                Swal.fire({
                  icon: 'error',
                  title: 'Oops...',
                  text: data['msg']?data['msg']:"Something went wrong!",
                })
              }
            }
            
          });
    })
}
function afterPopup(){
  $('.btn_remove_popup').on('click',function(){
    $('.vr_popup').remove();
  })
}