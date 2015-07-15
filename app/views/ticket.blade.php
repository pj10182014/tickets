@extends('layouts.master')
@section('css')
<style>
</style>
@stop

@section('contents')
<div class="container">
  <div class="title">
    <h1>Ticket search</h1>
  </div>
  <div class="update-info">
    <h3>{{$num}} files have been convert and updated.</h3>
  </div>
  <div class="sub-container">
    <div class="form-field">
      <label>Ticket Number : </label>
      <input class="ticket-field" type="text" name="ticketNumber" value="" placeholder="Please enter ticket number">
    </div>
    <div class="form-field">
      <label>Passenger Name : </label>
      <input class="name-field" type = "text" name="passengerName" value="" placeholder="Please enter passenger name">
    </div>
    <div class="form-field">
      <label>RLOC : </label>
      <input class="rloc-field" type = "text" name="rloc" value="" placeholder="Please enter rloc number">
    </div>
    <div class="button-field">
      <input type="submit" class="btn-search"value="Search">
      <button class="btn-update">Update</button>
      <button class="btn-prev" name="previous">PREV</button>
      <button class="btn-next" name="next">NEXT</button>
    </div>
    <input type="hidden" name="ticketHolder" value="">
  </div>
  <div id="text-field">

  </div>
</div>
@stop

@section('js')

<script>
  $(document).ready(function() {
    $( "#text-field" ).accordion();
    $('.btn-prev').attr('disabled','disabled');
//    $('.btn-next').attr('disabled','disabled');


    setTimeout(function() {
        $('.update-info').slideUp('slow');
    }, 1000);

    $("button,input[type=submit]").button()

    $(".btn-search").click(function(event) {
      event.preventDefault();
      var noError = true; 

      var ticketNumber   = $.trim($("input[name='ticketNumber']").val());
      var passengerName  = $.trim($("input[name='passengerName']").val());
      var rloc           = $.trim($("input[name='rloc']").val());

      if($.isNumeric(ticketNumber) || ticketNumber==""){
        noError = true;
      }else{
        noError = false;
        $("input[name='ticketNumber']").val('');
        alert("please enter a number");
      }

      if(noError){
        $("#text-field").empty();
        $.ajax({
          method: "post",
          url: "/search",
          dataType: "json",
          data: {ticketNumber:ticketNumber,
                 passengerName:passengerName,
                 rloc:rloc},
          success: function(data){
            if(data.length>1){
              $.each(data,function(index,item){
                $("#text-field").append("<h3 class='block-hearder'><span>"+item['dateOfFile']+"</span><span>"+item['paxName']+"</span><span>"+item['airlineName']+"</span></h3><div class='text-block'>"+item['content']+"</div>");
                searchNext(item['orderOfDay'],item['dateOfFile']);
              });

              $( "#text-field" ).accordion( "destroy" );
              $( "#text-field" ).accordion({
                collapsible: true
              });

            }else{
              $.each(data,function(index,item){
                $("#text-field").append("<div class='text-block-single'>"+item['content']+"</div>"+"<script>");
                searchNext(item['orderOfDay'],item['dateOfFile']);
              });
            }
            $("input[name='ticketNumber']").val('');
            $("input[name='passengerName']").val('');
            $("input[name='rloc']").val('');
          }
        });
      }
    });  //end btn-search


    $(".btn-update").click(function(event) {
      event.preventDefault();
      $.ajax({
        method: "get",
        url: "/update",
        dataType: "json",
        success: function(data){
          $(".update-info h3").text(data['num']+' files have been convert and updated.')
          $(".update-info").show();
          setTimeout(function() {
              $('.update-info').slideUp('slow');
          }, 1000);
        }
      });
    });  //end btn-update


    /*
     * searchNext()
     * Using orderOfDay to find the next ticket
     * After last orderOfDay is reached and this function is invoked, "Reached MAX record, Total records for the day: " will be printed
     * */
    function searchNext(orderOfDay,dateOfFile){
      var orderOfDay = Number(orderOfDay);  //Converting orderOfDay to Number because it is a string in the database
      var dateOfFile = dateOfFile;

      // Making sure this function doesn't get called twice
      if( !this.wasRun ){
        $(".btn-next").click(function(event) {
          event.preventDefault();
          $.ajax({
            method: "post",
            url: "/next",
            dataType: "json",
            data: {orderOfDay:orderOfDay, dateOfFile: dateOfFile},
            success: function(data){
              $("#text-field").empty();
              $("#text-field").append("<div class='text-block-single'>"+data['content']+"</div>");

              //Run the function again if orderOfDay's value is the same as orderOfDay value passed back
              if((orderOfDay + 1) == data['orderOfDay']){
                orderOfDay++;
                searchNext(orderOfDay);
              }
            }
          });
        });  //end btn-next
      }
      this.wasRun = true;
    }  //end searchNext()

     
  }); //end document ready
</script>
{{-- END PAGE LEVEL JAVASCRIPT --}}
@stop
