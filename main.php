<?php
  require_once dirname(__FILE__).'/'.'./lib/tmhOAuth/tmhOAuth.php';

  $tmhOAuth = new tmhOAuth(
	  array(
	  "consumer_key" => "",
	  "consumer_secret" => "",
	  "user_token" => "",
	  "user_secret" => "")
  );
  
  
  //UserStream接続してTweet受信毎にcallback関数を呼び出す
  $method = "https://userstream.twitter.com/1.1/user.json";
  $params = array();
  $tmhOAuth->streaming_request('POST', $method, $params, 'callback', true);
  
  function callback($data, $length, $metrics) {
  	global $tmhOAuth;

	  $jDecode = json_decode($data);
	  
	  //各アクションを実行
    /** 名前変更**/
    UpdateName($tmhOAuth,$jDecode,$your_screen_name);
	  
	  /** スクリーンネームとつぶやきを見る**/
	  Tweet_Watch($tmhOAuth,$jDecode);
	  
  }
  

  //名前変更
  //正規表現のスクリーンネーム部分は書き換えておいてね
  function UpdateName($obj,$jDecode){
    if (empty($jDecode->{"retweeted_status"})){
      if( preg_match('/[(（][@＠]mizofumi0411[)）]/', $jDecode->{"text"}, $m)  ){
        $name = preg_replace ( '/[(（][@＠]mizofumi0411[)）]/' , '' , $jDecode->{"text"} );
        $name = preg_replace ( '/[@＠]/' , '' , $name );
        $id = $jDecode->{"id_str"};
        UpdateNameRequest($obj,$name);
        Create_Favorite($obj,$id);
      }
    }
  }

  //Tweetを出力
  function Tweet_Watch($obj,$jDecode){
    $screen_name = $jDecode->{"user"}->{"screen_name"};
    $text = $jDecode->{"text"};
    if (!empty($text)){
      print "@$screen_name - $text \n";
    }
  }

  //Tweetをサーバに投げる
  function UpdateStatusRequest($obj,$text)
  {
    return $obj->request('POST', $obj->url('1.1/statuses/update'), array(
    'status' => $text
    ));
  }

  //ふぁぼる
  function Create_Favorite($obj,$id)
  {
    $obj->request('POST', $obj->url('1.1/favorites/create'), array(
    'id' => $id
    ));
  }

  //名前変更をサーバに投げる
  function UpdateNameRequest($obj,$name){
    if ( intval($obj->request('POST', $obj->url('1.1/account/update_profile'), array(
    'name' => $name
    ))) === 200){
      UpdateStatusRequest($obj,"$name に改名しました");
    }else{
      UpdateStatusRequest($obj,"$name に改名できませんでした");
    }
  }