<!DOCTYPE html>

<html lang = "en">

<head>

<meta charset="utf-8" />

<title>5 star test</title>

<style>

form {

  display: inline-block;

  font-size: 0;

}



form input[type="radio"] {

  display: none;

}



form label {

  display: inline-block;

  font-size: 50px;

  cursor: pointer;

  color: #ccc;

}



form input[type="radio"]:checked ~ label {

  color: #ffc107;

}



</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="jsFunctions.js"></script>

    

</head>

<body>

<form id = 'Submit'>

  <input type="radio" id="star1" name="rating" value="1" />

  <label for="star1">&#9733;</label>

  <input type="radio" id="star2" name="rating" value="2" />

  <label for="star2">&#9733;</label>

  <input type="radio" id="star3" name="rating" value="3" />

  <label for="star3">&#9733;</label>

  <input type="radio" id="star4" name="rating" value="4" />

  <label for="star4">&#9733;</label>

  <input type="radio" id="star5" name="rating" value="5" />

  <label for="star5">&#9733;</label>

  <input type="submit" value="Submit">

</form>

<p id="avgRating">Average rating: </p>

</body>

</html>
