<?php

namespace MantencionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use BaseBundle\Entity\Mantencion;
use \stdClass;

class AgregarMantencionController extends Controller
{
    public function agregarRutaAction(Request $request)
    {
    	$result = false;

    	if( $request->getMethod() == 'POST' )
        {
        	if($postdata = file_get_contents("php://input"))
            {
                // variables
                $em     = $this->getDoctrine()->getManager();
                $post   = json_decode($postdata);

                $key 	= $post->key;
                $codigo = eregi_replace("[a-zA-Z]","",$post->codigo);
                $lat    = $post->lat;
                $lng    = $post->lng;
                $user   = $post->usuario;
                $comentario = $post->comentario;
                // $foto   = $post->foto;

                if($key == '$2a$07$jpdeveloperstringforsOk0vkvMOVVB8r722xvEQLXl//e23GFq.' && $dnnb = $em->getRepository('BaseBundle:DetcontratoNnBanno')->findOneBy(array('dnnbBannoFk' => $codigo,'dnnbActivo' => 1 )) )
                {

                    $dia = new \DateTime(date("Y-m-d H:i:s"));
                    date_default_timezone_set("America/Santiago");
                    $ndia = date('N', strtotime($dia->format('Y-m-d')));

                    $idDetalle = $dnnb->getDnnbDetcontratoFk()->getDcoIdPk();

                    // foraneas
                    $fkRuta             = $em->getRepository('BaseBundle:Ruta')->findOneBy(array('rutDetallecontratoFk' => $idDetalle, 'rutDia' => $ndia, 'rutActivo' => 1 ));
                    $fkDetalleContrato  = $em->getRepository('BaseBundle:DetalleContrato')->findOneBy(array('dcoIdPk' => $dnnb->getDnnbDetcontratoFk() ));
                    $fkUsuario          = $em->getRepository('BaseBundle:Usuario')->findOneBy(array('usuIdPk' => $user ));

                    $mantencion = new Mantencion();

                    $mantencion->setManRutaFk($fkRuta);
                    $mantencion->setManUsuarioFk($fkUsuario);
                    $mantencion->setManNnbannoFk($dnnb);
                    $mantencion->setManDetallecontratoFk($fkDetalleContrato);
                    $mantencion->setManLat($lat);
                    $mantencion->setManLng($lng);
                    $mantencion->setManRealizado(1);
                    $mantencion->setManComentario($comentario);
                    $mantencion->setManFecharegistro(new \DateTime(date("Y-m-d H:i:s")));

                    $em->persist($mantencion);

                    if( !$fkDetalleContrato->getDcoLat() || !$fkDetalleContrato->getDcoLon() )
                    {
                        $fkDetalleContrato->setDcoLat($lat);
                        $fkDetalleContrato->setDcoLon($lng);
                        $em->persist($fkDetalleContrato);
                    }
                    
                    $em->flush();
                    $result = true;

                }

            }

        }

        echo json_encode(array('result' => $result));

        exit;
	}

    public function actualizarAction(Request $request)
    {
        $result = false;

        if( $request->getMethod() == 'POST' )
        {
            if($postdata = file_get_contents("php://input"))
            {
                $em     = $this->getDoctrine()->getManager();
                $post   = json_decode($postdata);
                $datos  = json_decode($post->datos);
                $lat    = $post->lat;
                $lng    = $post->lng;

                foreach($datos as $value)
                {
                    $f_cod      = eregi_replace("[a-zA-Z]","",$value->codigo);
                    $ndia       = date('N', strtotime($value->fecha));

                    if($dnnb = $em->getRepository('BaseBundle:DetcontratoNnBanno')->findOneBy(array('dnnbBannoFk' => $f_cod,'dnnbActivo' => 1 )))
                    {
                        $idDetalle = $dnnb->getDnnbDetcontratoFk()->getDcoIdPk();
                        // foraneas
                        $fkUsuario = $em->getRepository('BaseBundle:Usuario')->findOneBy(array('usuIdPk' => $value->usuario ));
                        $fkRuta    = $em->getRepository('BaseBundle:Ruta')->findOneBy(array('rutDetallecontratoFk' => $idDetalle, 'rutDia' => $ndia, 'rutActivo' => 1 ));
                        $fkDetalleContrato  = $em->getRepository('BaseBundle:DetalleContrato')->findOneBy(array('dcoIdPk' => $dnnb->getDnnbDetcontratoFk() ));

                        $f_fecha    = $value->fecha;
                        $flat       = ($value->lat != 0)?$value->lat:$lat;
                        $flng       = ($value->lng != 0)?$value->lng:$lng;
                        $coment     = $value->comentario;
                        
                        $mantencion = new Mantencion();

                        $mantencion->setManRutaFk($fkRuta);
                        $mantencion->setManUsuarioFk($fkUsuario);
                        $mantencion->setManNnbannoFk($dnnb);
                        $mantencion->setManDetallecontratoFk($fkDetalleContrato);
                        $mantencion->setManLat($flat);
                        $mantencion->setManLng($flng);
                        $mantencion->setManRealizado(1);
                        $mantencion->setManComentario($coment);
                        $mantencion->setManFecharegistro(new \DateTime($f_fecha));

                        $em->persist($mantencion);
                        
                    }
                }

                $em->flush();
                $result = true;
            }
        }

        echo json_encode(array('result' => $result));
        exit;
    }

	public function subirFotoAction()
	{
		$result = true;

		echo json_encode(array('result' => $result));
		exit;
	}
}