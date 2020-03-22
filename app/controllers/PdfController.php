<?php 
namespace App\Controllers; 
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Core\Controller;
use App\Models\Sales; // esto viene del MODELO       
use App\Models\Company; // esto viene del MODELO           
use App\Models\Orders; // esto viene del MODELO         
use App\Models\Pedidos; // esto viene del MODELO        

class PdfController extends  Controller {

    public function comprobante($serie,$numero,$tipo){
        $data_comprobante = Sales::salesall(strtoupper($serie),$numero)
                ->user()->customer()
                ->get();
        $data_empresa = Company::select('ruc','razon_social','logo','nombre_comercial',
                        'address','telefono','email','ubigeo_id')->get()->toArray();
        // \jsonCodeApi($data_comprobante);die();
        if($data_comprobante->count() == 0){echo  "<h2>No se Encontro Documento: ".ucwords($serie)."-$numero </h2>"; return;}
        $name_comprobante = $data_comprobante[0]['serie'] .'-'.$data_comprobante[0]['numero'];
       
        $dompdf = new Dompdf;
        $dompdf->loadHtml($this->ticketc($data_empresa,$data_comprobante));
        $dompdf->setPaper(array(0,0,224,1000));
        $dompdf->render();
        $dompdf->stream($name_comprobante, array("Attachment" => false));
          
     
    }

    public function ticketc($dataEmpresa,$dataComprobante){
      
        $html = file_get_contents(RESOURCES.'views/pdf/ticket/index_ticket.html');

        // EMPRESA 
        $html = str_replace('{{logo}}',\substr($dataEmpresa[0]['logo'],7),$html);
        $html = str_replace('{{nombre_comercial}}',$dataEmpresa[0]['nombre_comercial'],$html);
        $html = str_replace('{{direccion}}',$dataEmpresa[0]['address'],$html);
        $html = str_replace('{{telefono}}',$dataEmpresa[0]['telefono'],$html);
        
        // CLIENTE
        $html = str_replace('{{tipo_doc}}',strtoupper(TIPO_COMPROBANTE),$html);
        $html = str_replace('{{serie_numero}}',$dataComprobante[0]['serie'].'-'.$dataComprobante[0]['numero'],$html);
        $html = str_replace('{{fecha_emision}}',$dataComprobante[0]['created_at'],$html);
        $html = str_replace('{{cliente}}',ucwords ($dataComprobante[0]['customer']['business_name']),$html);
        $html = str_replace('{{num_doc_cliente}}',$dataComprobante[0]['customer']['doc'],$html);
        if($dataComprobante[0]['customer']['document_id'] == 1){
            $html = str_replace('{{typedoc}}','DNI',$html);
        }else if($dataComprobante[0]['customer']['document_id'] == 2){
            $html = str_replace('{{typedoc}}','RUC',$html);
        }else{
            $html = str_replace('{{typedoc}}','',$html);
        }
        $html = str_replace('{{direccion_cliente}}',$dataComprobante[0]['customer']['address'],$html); 

        // PRODUCTOS
        $str_productos = '';
        $productospe = json_decode($dataComprobante[0]['products_sales']);
        foreach($productospe as $key => $value){
            $str_item = file_get_contents(RESOURCES.'views/pdf/ticket/productos_ticket.html');
            $str_item = \str_replace('{{descripcion}}',$value->name,$str_item);
            $str_item = \str_replace('{{valor_unitario}}',$value->price,$str_item);
            $str_item = \str_replace('{{cantidad}}',$value->cantidad,$str_item);
            $str_item = \str_replace('{{valor_total}}', number_format($value->cantidad * $value->price,2,'.',',') ,$str_item);
            $str_productos = $str_productos.$str_item;
        }
        $html = str_replace('{{productos}}',$str_productos,$html);
        $html = str_replace('{{total}}','S/. '.number_format($dataComprobante[0]['total'],2,'.',','),$html);
        return $html;
    }


    public function reportsale($dateinit,$datefin){  
        $data_empresa = Company::select('ruc','razon_social','logo','nombre_comercial',
                        'address','telefono','email','ubigeo_id')->get()->toArray();
        if($dateinit == $datefin){
            $data_report = Sales::select('serie','numero','products_sales','total','cliente_id','created_at')
                    ->with(['customer:id,doc,business_name'])
                    ->where('deleted_at','=',0,'and')
                    ->where('state',1,'and')
                    ->whereDate('created_at',$dateinit)->get();
        }else{
            $data_report = Sales::select('serie','numero','products_sales','total','cliente_id','created_at')
                        ->with(['customer:id,doc,business_name'])
                        ->where('deleted_at','=',0,'and')
                        ->whereBetween('created_at',[$dateinit, $datefin])
                        ->get();

        }
        // \jsonCodeApi($data_report); die();

        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_paper("A4","landscape"); //portrait
        $dompdf->loadHtml($this->reporte_ventas($data_report,$data_empresa,$dateinit,$datefin));
        $dompdf->render();
        $dompdf->stream('ReporteVentas '.$dateinit.' al '.$datefin, array("Attachment" => false));
    }

    public function reporte_ventas($datareport,$dataempresa,$dateinit,$datefin){
        // return count($datareport); die();
        $html = file_get_contents(RESOURCES.'views/pdf/pdf/report_index.html');
        $html = str_replace('{{link_css}}',RESOURCES.'views/pdf/pdf/styles.css',$html);

        // DATA EMPRESA \strip_tags($dataempresa[0]['nombre_comercial']) eliminamos la etiqueta <br>
        $html = str_replace('{{nombre_comercial}}', $dataempresa[0]['nombre_comercial'] ,$html);
        $html = str_replace('{{fechas}}',$dateinit.' al '.$datefin,$html);
        $html = str_replace('{{titulo_reporte}}',"REPORTE DE VENTAS",$html);

        // DATA VENTA
        $sale_row_products =''; 
        for ($i=0; $i < count($datareport); $i++) { 
            $str_talbepro = \file_get_contents(RESOURCES.'views/pdf/pdf/table_report.html');
            $str_talbepro = str_replace('{{id}}',$i + 1,$str_talbepro);
            $str_talbepro = str_replace('{{fecha_emitido}}',$datareport[$i]['created_at'],$str_talbepro);
            $str_talbepro = str_replace('{{serie}}',$datareport[$i]['serie'],$str_talbepro);
            $str_talbepro = str_replace('{{numero}}',$datareport[$i]['numero'],$str_talbepro);
            $str_talbepro = str_replace('{{cliente}}',$datareport[$i]['customer']['business_name'],$str_talbepro);
            $producspe = json_decode($datareport[$i]['products_sales']);
            $str_productos = '';  
            foreach($producspe as $key => $value){
                $item = \file_get_contents(RESOURCES.'views/pdf/pdf/products_sale.html');
                $item = str_replace('{{codigo}}',$value->code,$item);
                $item = str_replace('{{nombre_producto}}',$value->name,$item);
                $item = str_replace('{{cantidad}}',$value->cantidad,$item);
                $item = str_replace('{{precio}}','S/. '.$value->price,$item);
                $item = str_replace('{{sub_total}}','S/. '.number_format($value->price*$value->cantidad,2,'.',','),$item);
                $str_productos = $str_productos.$item;
            }
            $str_talbepro = str_replace('{{productos_vendidos}}',$str_productos,$str_talbepro);
            $str_talbepro = str_replace('{{total}}','S/. '.number_format($datareport[$i]['total'],2,'.',','),$str_talbepro);
            $sale_row_products = $sale_row_products.$str_talbepro;
        }

        $html = str_replace('{{table_products_sale}}',$sale_row_products,$html);
       
       
        return $html;
        
    }


    // PDF ORDER USER 
    public function orderpdfuser($id){
        $data_order = Orders::select('pedido','user_id','state','updated_at')
                    ->where('user_id',$id)
                    ->user()
                    ->first();

        $dompdf = new Dompdf;
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($this->order_user_pdf($data_order));
        $dompdf->set_paper("A4","portrait"); //portrait || landscape
        $dompdf->render();
        $dompdf->stream("Pedidos - ".ucfirst($data_order->user->name).' '.ucfirst($data_order->user->surname), array("Attachment" => false));
        // \jsonCodeApi($order);
    }
    public function order_user_pdf($dataOrder){
        $html = file_get_contents(RESOURCES.'views/pdf/order/user/index.html');
        $html = str_replace('{{link_css}}',RESOURCES.'views/pdf/order/user/styles.css',$html);
        //USER
        $html = \str_replace('{{titulo_r}}',"LISTADO DE PEDIDOS",$html);
        $html = \str_replace('{{dato_usuario}}',strtoupper($dataOrder->user->name).' '.strtoupper($dataOrder->user->surname),$html);
        $html = \str_replace('{{fecha_finalizado}}',strtoupper($dataOrder->updated_at),$html);

        if($dataOrder->pedido == null){
            $html = \str_replace('{{products}}','',$html);
            return $html;
        }
        // Products
        $products = \json_decode($dataOrder->pedido);
        $order_produts =''; $e=1;
        foreach($products as $key => $value){
            $item = \file_get_contents(RESOURCES.'views/pdf/order/user/products.html');
            $item = \str_replace('{{id}}',$e++,$item);
            $item = \str_replace('{{name_product}}',$value->name,$item);
            $item = \str_replace('{{price}}',"S/. ".$value->price,$item);
            $item = \str_replace('{{count_produtc}}',$value->cantidad. ' '.$value->unit->name,$item);
            $order_produts = $order_produts.$item;
        }

        $html = \str_replace('{{products}}',$order_produts,$html);

        return $html;
    }


    // REPORTE MERCADERIA
    // LISTADO DE PEDIDOS - ESTO MOSTRARA LA CANTIDAD DE PRODUCTOS A RETIRAR DEL ALMACEN
    public function reporte_mercaderia(){
        $pedidos = Pedidos::producto()->get();  
        // \jsonCodeApi($pedidos); die();
        $dompdf = new Dompdf;
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->loadHtml($this->pedidos_mercaderia($pedidos));
        $dompdf->set_paper("A4","portrait"); //portrait || landscape
        $dompdf->render();
        $dompdf->stream("Pedidop", array("Attachment" => false));
        // \jsonCodeApi($pedidos);
    }
    public function pedidos_mercaderia($data){
        $html = file_get_contents(RESOURCES.'views/pdf/mecaderia/index.html');
        $html = str_replace('{{link_css}}',RESOURCES.'views/pdf/mecaderia/styles.css',$html);
        
        $html = \str_replace('{{titulo_r}}','REPORTE DE MERCADERIA',$html);
        
        //PRODUCTOS 
        $order_produts =''; $e=1;
        foreach($data as  $key => $value){
            $item = \file_get_contents(RESOURCES.'views/pdf/mecaderia/products.html');
            $item = \str_replace('{{id}}',$e++,$item);
            $item = \str_replace('{{name_product}}',$value->product->name,$item);
            $item = \str_replace('{{price}}',"S/. ".$value->product->price, $item);
            $item = \str_replace('{{count_produtc}}',$value->cantidad. ' '. $value->product->unit->name,$item);
            $order_produts = $order_produts.$item;
        }

        $html = \str_replace('{{products}}',$order_produts,$html);
        

        return $html;
    }
}