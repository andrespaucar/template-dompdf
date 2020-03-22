<?php 

namespace App\Core;

class View {
    
    public function render($ruta,$data= []){
        $path = str_replace('.',DS,$ruta);
        //__DIR__.'/../../resources/views/layouts/footer.php';
        if(!file_exists(VIEWS.$path.'.php')){
            printf('<code>No existe la vista en la ruta : <b>%s</b></code>',VIEWS.$path.'.php');
            die();
        }
        require_once(VIEWS.$path.'.php');

        exit();
    }
}