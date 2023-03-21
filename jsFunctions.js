function request()

{

  var curBrowser;

  var whichBrowser;

  whichBrowser = navigator.appName;

  if (whichBrowser == "MicrosoftInternetExplorer")

  {

    curBrowser  = ActiveXObject("Microsoft.XMLHTTP");

  }

  else 

  {

    curBrowser = new XMLHttpRequest();

  }

  return curBrowser;

}



$(function() 

{

  $('#Submit').on('submit', function(e) 

  {

  

    e.preventDefault();

    var rating = $('input[name="rating"]:checked').val();

    submitRating(rating);

  });

});



function submitRating(rating) 

{

  $.ajax(

  {

    url: 'ajax.php',

    type: 'POST',

    data: {rating: rating},

    success: function(data) 

    {

      $('#avgRating').html(data);

    }

  });

}
