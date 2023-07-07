<?php

namespace App\Controllers;

use Core\Controller;
use Core\Http\Res;
use Core\Pipes\Pipes;
use Core\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

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


  function writeToImage($name)
  {
    $imagePath = 'Public/invitation.jpg';
    $image = imagecreatefromjpeg($imagePath);

    $fontFamily = 'Public/fonts.ttf';
    $fontSize = 65;

    $text = strtoupper($name);
    $textColor = imagecolorallocate($image, 0, 0, 0);

    $imageX = imagesx($image);
    $imageY = imagesy($image);

    $positionX = (int) ($imageX * 0.28);
    $positionY = (int) ($imageY * 0.278);

    imagettftext($image, $fontSize, 0, $positionX, $positionY, $textColor, $fontFamily, $text);

    ob_start();
    imagejpeg($image);
    $imgData = ob_get_clean();
    imagedestroy($image);
    $b64 = base64_encode($imgData);
    // header('Content-Type: image/jpg');ﬁ
    return $b64;
  }

  public function save($data)
  {

    $fData = [
      'name' => $data->name,
      'email' => $data->email,
    ];
    $dir = 'Public/invitations/';

    $file = $dir . $data->side . '.json';
    // echo $file;
    if (!file_exists($file)) {
      if (!is_dir($dir)) mkdir($dir, 0777);
      file_put_contents($file, json_encode([$fData]));
      Res::send("Successfully saved");
    } else {
      $content = (array) json_decode(file_get_contents($file));
      $length = count($content);
      $content[$length] = $fData;
      file_put_contents($file, json_encode($content, JSON_PRETTY_PRINT));
      Res::send($content);
    }
  }


  function invitations()
  {
    $path = 'Public/invitations/';
    $data = [];
    if (is_dir($path)) {
      $files = scandir($path);
      foreach ($files as $file) :
        if ($file === '.' || $file === '..') continue;
        $name = explode('.', $file);
        $data[$name[0]] = json_decode(file_get_contents($path . $file));
      endforeach;
      $this->toExcel($data);
    } else {
      Res::send("No invitations Yet");
    }
  }

  function toExcel($data)
  {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'BRIDE');
    $sheet->setCellValue('D1', 'GROOM');
    $sheet->setCellValue('A2', 'Name');
    $sheet->setCellValue('B2', 'Email');
    $sheet->setCellValue('D2', 'Name');
    $sheet->setCellValue('E2', 'Email');

    // For Bride
    if (isset($data['bride'])) :
      $i = 3;
      foreach ($data['bride'] as $info) :
        $sheet->setCellValue("A$i", $info->name);
        $sheet->setCellValue("B$i", $info->email);
        $i++;
      endforeach;
    endif;
    // For Groom
    if (isset($data['groom'])) :
      $i = 3;
      foreach ($data['groom'] as $info) :
        $sheet->setCellValue("D$i", $info->name);
        $sheet->setCellValue("E$i", $info->email);
        $i++;
      endforeach;
    endif;

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="invitation.xlsx"');
    $writer->save('php://output');
  }
}
