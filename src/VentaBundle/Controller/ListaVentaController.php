<?php

namespace VentaBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use \stdClass;

class ListaVentaController extends Controller
{
/**
VISTAS
**/
    public function listaAction()
    {
        // validar session
        if(!$this->get('service.user.data')->ValidarSession('Arriendos')){return $this->redirectToRoute('base_vista_ingreso');}

        // servicios
        $defaultData = $this->get('service.default.data');
        $defaultData->setHtmlHeader(array('title' => 'Inicio'));
        $userData = $this->get('service.user.data');
        
        return $this->render('VentaBundle::listaVenta.html.twig', array(
            'defaultData'   => $defaultData->getAll(),
            'userData'      => $userData->getUserData(),
            'menuActivo'    => 'listaventa'
            ));
    }
/**
FUNCIONES AJAX
*/
	public function cargarListaAction()
	{
        // validar session
        if(!$this->get('service.user.data')->ValidarSession('Arriendos')){return $this->redirectToRoute('base_vista_ingreso');}

        // servicios
        $userData = $this->get('service.user.data');
        
		// variables
		$em     = $this->getDoctrine()->getManager();
        $qb     = $em->createQueryBuilder();
        $where	= 'v.venSucursalFk = '.$userData->getUserData()->sucursalActiva;

        // cargar lista
        $q  = $qb->select(array('v'))
                    ->from('BaseBundle:Venta', 'v')
                    ->where($where)
                    ->orderBy('v.venIdPk', 'DESC')
                    // ->setFirstResult( $pagina )
                    // ->setMaxResults( $limite )
                    ->getQuery();
        $resultQuery = $q->getResult();

        if($resultQuery)
        {
        	$cargarLista = '';
        	foreach($resultQuery as $value)
        	{
                // botones
                $bottonRuta = '';
                $bottonBannos = '';

                // detalle venta
                $detDireccion = '';
                $detServicio  = '';
                $detCantidad  = 0;
                if($detalleVenta = $em->getRepository('BaseBundle:DetalleContrato')->findBy(array('dcoVentaFk' => $value->getVenIdPk())))
                {
                    foreach($detalleVenta as $value2)
                    {
                        // total de productos
                        $total = $value2->getDcoCbano()+$value2->getDcoCcaseta()+$value2->getDcoCducha()+$value2->getDcoCexterno()+$value2->getDcoClavamano();
                        // asignacion de baños completa o sin asignar
                        if($dnnb = $em->getRepository('BaseBundle:DetcontratoNnBanno')->findBy(array('dnnbDetcontratoFk' => $value2->getDcoIdPk() )))
                        {
                            $countDnnb = 0;
                            foreach($dnnb as $value3)
                            {
                                if($value3->getDnnbActivo() == 1)
                                {
                                    $countDnnb ++;
                                }
                            }


                            if($countDnnb < $total)
                            {
                                $bottonBannos = '<a href="'.$this->get('router')->generate('mantencion_vista_asignarbannos', array('id' => $value->getVenIdPk() )).'" class="btn btn-sm btn-warning pulsate" data-toggle="tooltip" data-container="body" data-original-title="Asignar baños"><i class="fa fa-check-circle-o"></i></a>';
                            }

                        }else{
                            $bottonBannos = '<a href="'.$this->get('router')->generate('mantencion_vista_asignarbannos', array('id' => $value->getVenIdPk() )).'" class="btn btn-sm btn-warning pulsate" data-toggle="tooltip" data-container="body" data-original-title="Asignar baños"><i class="fa fa-check-circle-o"></i></a>';
                        }

                        // cargar datos detalle
                        $detServicio    = '<p>'.$value2->getDcoServicioFk()->getSerNombre().'</p>';
                        $detDireccion   .= '<p>'.$value2->getDcoDireccion().', '.$value2->getDcoComunaFk()->getComNombre().', '.$value2->getDcoComunaFk()->getComProvinciaFk()->getProNombre().', '.$value2->getDcoComunaFk()->getComProvinciaFk()->getProRegionFk()->getRegNombre().'</p>';
                        $detCantidad    += $total;

                        // botones de ruta y asignacion de baños
                        if($rutaDetalle = $em->getRepository('BaseBundle:Ruta')->findBy(array('rutDetallecontratoFk' => $value2->getDcoIdPk())))
                        {
                            foreach($rutaDetalle as $value3)
                            {
                                // asignacion de camiones completa o incompleta
                                if(!$value3->getRutCamionFk())
                                {
                                    $bottonRuta = '<a href="'.$this->get('router')->generate('mantencion_vista_agregarruta', array('id' => $value->getVenIdPk() )).'" class="btn btn-sm btn-warning pulsate" data-toggle="tooltip" data-container="body" data-original-title="Agregar a la ruta"><i class="fa fa-road"></i></a>';
                                }
                            }
                        }


	        		}
        		}

        		// crear fila
        		$cargarLista.= '<tr>';
        		$cargarLista.= '<td>'.$value->getVenFechainicio()->format('d/m/Y').'</td>';
        		$cargarLista.= '<td>'.$value->getVenClienteFk()->getCliNombre().'</td>';
        		$cargarLista.= '<td>'.$detServicio.'</td>';
        		$cargarLista.= '<td>'.$detDireccion.'</td>';
        		$cargarLista.= '<td>'.$detCantidad.' Productos</td>';

        		// botones de operaciones
        		$cargarLista.= '<td class="text-right"><div class="btn-group">';
        		// $cargarLista.= '<a href="#" class="btn btn-sm btn-warning pulsate" data-toggle="tooltip" data-container="body" data-original-title="Asignar baños"><i class="fa fa-check-circle-o"></i></a>';
                $cargarLista.= $bottonBannos;
        		$cargarLista.= $bottonRuta;
        		$cargarLista.= '<a href="'.$this->get('router')->generate('venta_vista_verventa', array('id' => $value->getVenIdPk() )).'" class="btn btn-sm btn-default"><i class="fa fa-search"></i></a>';
        		$cargarLista.= '<button type="button" data-id="" class="btn btn-sm btn-danger button_eliminarVenta"><i class="fa fa-power-off"></i></button>';
        		$cargarLista.= '</div></td>';
        		$cargarLista.= '</tr>';

        	}

        }else{
            $cargarLista = false;
        }

		$result = true;

		echo json_encode(array('result' => $result, 'cargarlista' => $cargarLista));
		exit;
	}
}
