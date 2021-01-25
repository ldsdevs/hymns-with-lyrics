<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

$path=$_SERVER["PATH_INFO"];
$params=$_GET;


function processUrl($url){
    $content=file_get_contents($url);
    $doc = new DOMDocument();
    $doc->strictErrorChecking = false;
    $doc->loadHTML($content);
    $xpath = new DOMXpath($doc);
    // lyrics
    $elements = $xpath->query("//div[@class='song-primary']");
    $lines = explode("\n",$elements->item(0)->textContent);
    $lyrics = [];
    foreach($lines as $line){
        if (trim($line)!==""){
            $lyrics[]=trim($line);
        }
    }
    // media
    $elements2 = $xpath->query("//ul[@class='song-type']/li/a[@class='play']");
    $mediaUrl = ( $elements2->item(0)->attributes["href"]->nodeValue);
    // pdf
    $elements2 = $xpath->query("//ul[@class='song-type']/li/a[@class='pdf']");
    $pdfUrl = ( $elements2->item(0)->attributes["href"]->nodeValue);
    
    return [
        "lyrics" => $lyrics,
        "media" => $mediaUrl,
        "pdf" => $pdfUrl
    ];
}

function update(){
    $filename="json/hymns-es.json";
    $content=file_get_contents($filename);
    $items=json_decode($content,true);
    foreach($items as $idx=>$item){
        if (@$item["media"]){
            continue;
        }
        echo "Processing $idx\n";
        $result = processUrl($item["url"]);
        foreach($result as $key=>$res){
            $items[$idx][$key]=$result[$key];
        }
        file_put_contents($filename,json_encode($items,JSON_PRETTY_PRINT));
        sleep(1);
    }
}


if($_SERVER["argv"][1]=="update"){
    update();
    die();
}


$response = [
    "path" => $path,
    "params" => $params
];

if ($params["action"]=="data"){
    $response = processUrl($params["url"]);
}
header("Content-type: text/json");
echo json_encode($response);