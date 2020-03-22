# PLANTILLA PARA DOMPDF
Adaptable para cualquier otro proyecto, es parte del proyecto que desarrolle de un sistemas de ventas e inventario.

El controllador toma la vista que es un HTML maquetado en una tabla para el **Reporte de ventas** luego el controllador lo reemplaza las variables que estan en doble parentesis **{{titulo}}** para reemplazar **str_replace()** por el dato que se obtubo en la base de datos. Esto de maquetar en la vista el PDF y luego cambiar solo los datos que queremos nos permite hacer diseños un poco mas complejo y separamos la vista de la logica pero se tendra el mismo pdf evitando complicacion y errores.
La verdadera vista del PDF lo realiza desde los metodos que brinda DomPdf, sino que solo obtenemos el **HTML** como un recurso para luego que esta sea renderizados.

_Espero que te sea de ayuda_

![Cat](Captura%20de%20pantalla.png)