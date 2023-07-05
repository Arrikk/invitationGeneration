<?php
namespace App\Controllers;

use Core\Controller;
use Core\Http\Res;
use Core\Pipes\Pipes;
use Core\View;

class Home extends Controller
{
    public function index()
    {
      return View::page('index.html');
    }

    public function process(Pipes $data)
    {
      $image = $this->writeToImage($data->name);
      Res::json([
        'image' => $image,
        'name' => $data->name,
        'email' => $data->email,
        'side' => $data->side
      ]);

    }


    function writeToImage($name) {
      $imagePath = 'Public/invitation.jpg';
      $image = imagecreatefromjpeg($imagePath);

      $fontFamily = 'Public/font.otf';
      $fontSize = 65;

      $text = $name;
      $textColor = imagecolorallocate($image, 0, 0, 0);

      $imageX = imagesx($image);
      $imageY = imagesy($image);

      $positionX = (int) ($imageX * 0.4);
      $positionY = (int) ($imageY * 0.278);

      imagettftext($image, $fontSize, 0, $positionX, $positionY, $textColor, $fontFamily, $text);

      ob_start();
      imagejpeg($image);
      $imgData = ob_get_clean();
      imagedestroy($image);
      $b64 = base64_encode($imgData);
      // header('Content-Type: image/jpg');
      return $b64;
    }

    public function save($data)
    {

      $fData = [
        'name' => $data->name,
        'email' => $data->email,
      ];
      $dir = 'Public/invitations/';

      $file = $dir.$data->side.'.json';
      // echo $file;
      if(!file_exists($file)){
       if(!is_dir($dir)) mkdir($dir, 0777);
        file_put_contents($file, json_encode([$fData]));
        Res::send("Successfully saved");
      }else{
        $content = (array) json_decode(file_get_contents($file));
        $length = count($content);
        $content[$length] = $fData;
        file_put_contents($file, json_encode($content, JSON_PRETTY_PRINT));
        Res::send($content);

      }
      
    }


    function invitations() {
      $path = 'Public/invitations/';
      $data = [];
      if(is_dir($path)){
        $files = scandir($path);
        foreach($files as $file):
          if($file === '.' || $file === '..') continue;
          $name = explode('.', $file);
          $data[$name[0]] = json_decode(file_get_contents($path.$file));
        endforeach;
        Res::json($data);
      }else{
        Res::send("No invitations Yet");
      }
    }
}