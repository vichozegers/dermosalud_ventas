<?php
// Contenido del fichero /application/controllers/jugadores.php
defined('BASEPATH') OR exit('No direct script access allowed');
 
 
class Informe extends CI_Controller {
 
    public function __construct(){
	parent::__construct();
        // Models
       
        // Libraries
        $this->load->library('excel');
      
        $this->load->helper('ln');
    }
 
   
   

 
public function exportar2excel($fi,$ft,$id){
        
       ob_start();
#Agregar a futuro tienda      
$ventas= Sale::find_by_sql("SELECT * FROM zarest_sales zs,zarest_sale_items zsi WHERE zs.created_at>='".$fi." 00:00:00' AND zs.created_at<= '".$ft."  23:59:59' AND zs.id=zsi.sale_id ORDER BY zs.created_at ASC ");       

$objPHPExcel = new PHPExcel();

// Add some data
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'FECHA')
            ->setCellValue('B1', 'VENDEDOR')
            ->setCellValue('C1', 'CLIENTE')
            ->setCellValue('D1', 'PRODUCTO') 
            ->setCellValue('E1', 'CANTIDAD')
            ->setCellValue('F1', 'PRECIO')
            ->setCellValue('G1', 'SUBTOTAL')
            ->setCellValue('H1', 'IVA')
            ->setCellValue('I1', 'TOTAL FINAL');
            //->setCellValue('J1', 'PAGADO');

$objPHPExcel->getActiveSheet()
    ->getStyle('A1:J1')
    ->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('66CCFF');

  foreach (range('A', $objPHPExcel->getActiveSheet()->getHighestDataColumn()) as $col) {
        $objPHPExcel->getActiveSheet()
                ->getColumnDimension($col)
                ->setAutoSize(true);
         
    } 


// Miscellaneous glyphs, UTF-8
$fila=2;
       foreach ($ventas as $venta) {
    $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$fila,$venta->date); 
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$fila,$venta->created_by);
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$fila,$venta->clientname);
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$fila,$venta->name);
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$fila,$venta->qt);
         $total_items=$total_items+$venta->totalitems;
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$fila,$venta->price);
         $price=$price+$venta->price;
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$fila,$venta->subtotal);
         $subtotal=$subtotal+$venta->subtotal;
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$fila,$venta->tax);
         $imp=(($venta->subtotal*$venta->tax)/100);
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$fila,$venta->subtotal+$imp);
         $total=$total+($venta->subtotal+$imp);
         //$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$fila,$venta->paid);  
         $paid=$paid+$venta->paid;
         $fila++;
}
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$fila,$total_items);
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$fila,$price);
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$fila,$subtotal);
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$fila,$venta->tax);
         $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$fila,$total);
        // $objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$fila,$paid);  
        




$objPHPExcel->getActiveSheet()
    ->getStyle('A'.$fila.':J'.$fila)
    ->getFill()
    ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('66CCFF');        
            
           

// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle('Ventas');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
header('Content-Type: application/vnd.ms-excel');

header('Content-Disposition: attachment;filename="ventas.xls"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0
 $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

$ruta='files/ventas_' . $fi .'_'.$ft.'_'.rand().'.xls';
$objWriter->save($ruta); 
#$this->load->helper('files/ventas_' . $fi .'_'.$ft.'_'.rand().'.xls');
#$this->load->helper('download');
#echo $path=file_get_contents(base_url().$ruta);
#force_download('ventas_' . $fi .'_'.$ft.'_'.rand().'.xls', $path);
#$objWriter->save('php://output');
#download($ruta);
echo $ruta;


    }
 
    
}