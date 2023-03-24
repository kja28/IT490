<?php function search($queryx){


$query = urlcode($queryx);

$curl = curl_init();
	

curl_setopt_array($curl, [
	CURLOPT_URL =>  "https://yummly2.p.rapidapi.com/feeds/auto-complete?q={$query}",
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_ENCODING => "",
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 30,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => "GET",
	CURLOPT_HTTPHEADER => [
		"X-RapidAPI-Host: yummly2.p.rapidapi.com",
		"X-RapidAPI-Key: f3e58460f7msh59bd4ab7d8245aap10982bjsn0b20d69d0da7"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

 if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        echo $response;
    }
    
    
    $response_data = json_decode($response, true);

    
    return $response_data['value'];
}
