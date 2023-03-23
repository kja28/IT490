<?php function getMeal($query){

	$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL => "https://random-recipes.p.rapidapi.com/ai-quotes/$query",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => [
		"X-RapidAPI-Host: random-recipes.p.rapidapi.com",
		"X-RapidAPI-Key: f3e58460f7msh59bd4ab7d8245aap10982bjsn0b20d69d0da7"
	],
]);
$response = curl_exec($curl);
//$err = curl_error($curl);

curl_close($curl);

echo $response;

$response_object = json_decode($response);
return $response_object->value;


}
