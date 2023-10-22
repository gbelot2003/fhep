<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
/*
 *  nrutaf Usado cuando creamos una ruta para un nuevo controlador. ejm. http://miweb.com/micontrolador
 * 
 *  nruta Usado para agregar subrutas a una ruta de un controlador que ya existe. ejm. http://miweb.com/micontrolador/miaction
 * 
 *  nrutae Usado para agregar subrutas a una ruta de un controlador que ya existe pero que lleva paráSmetro por url. ejm. http://miweb.com/micontrolador/miaction/id
 * 
 *          donde id es el parámetro
 */

/*
  //Ruta para el controlador Micontrolador nrutaf
  'Micontrolador' => [
  'type' => 'Zend\Mvc\Router\Http\Literal',
  'options' => [
  'route' => '/dependencias',
  'defaults' => [
  '__NAMESPACE__' => 'Application\Controller',
  'controller' => 'Micontrolador',
  'action' => 'dependencias',
  ],
  ],
  'may_terminate' => true,
  'child_routes' => [
  //SUB RUTAS
  //Subruta  nuevo nruta
  'nuevo' => [
  'type' => 'Segment',
  'options' => [
  'route' => '/nuevo',
  'defaults' => [
  'controller' => 'Application\Controller\Micontrolador',
  'action' => 'nuevo',
  ],
  ],
  ],
  //Fin subruta nuevo
  //Subruta editar nrutae
  'editar' => [
  'type' => 'Segment',
  'options' => [
  'route' => '/editar/:id',
  //Aqui los contraints
  'constraints' => [
  'id' => '[0-9]+', //Expresión regular que acepta
  ],
  'defaults' => [
  'controller' => 'Application\Controller\Micontrolador',
  'action' => 'editar',
  ],
  ],
  ],
  //Fin Subruta editar


  //FIN SUBRUTAS
  ],
  ], //Fin ruta del controlador Micontrolador
 */

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            //Ruta para el controlador Reportes
            'Reportes' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/reportes',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Reportes',
                        'action' => 'reportes',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //SUB RUTAS
                    //Subruta  gettablavacia
                    'gettablavacia' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablavacia',
                            'defaults' => [
                                'controller' => 'Application\Controller\Reportes',
                                'action' => 'gettablavacia',
                            ],
                        ],
                    ],
                    //Fin subruta gettablavacia
                    //Subruta gettablarepafiliados
                    'gettablarepafiliados' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablarepafiliados/:fi/:ff',
                            'defaults' => [
                                'controller' => 'Application\Controller\Reportes',
                                'action' => 'gettablarepafiliados',
                            ],
                        ],
                    ],
                    //Fin Subruta gettablarepafiliados
                    //Subruta gettablarepcitas
                    'gettablarepcitas' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablarepcitas/:fi/:ff',
                            'defaults' => [
                                'controller' => 'Application\Controller\Reportes',
                                'action' => 'gettablarepcitas',
                            ],
                        ],
                    ],
                    //Fin Subruta gettablarepcitas
                    //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador Reportes
            //Ruta para el controlador Usuarios
            'Usuarios' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/usuarios',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Usuarios',
                        'action' => 'Usuarios',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //SUB RUTAS
                    //Subruta  login
                    'login' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/login',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'login',
                            ],
                        ],
                    ],
                    //Fin subruta login
                    //Subruta  primerlogin
                    'primerlogin' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/primerlogin',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'primerlogin',
                            ],
                        ],
                    ],
                    //Fin subruta primerlogin
                    //Subruta  agregarusuario
                    'agregarusuario' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/agregarusuario',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'agregarusuario',
                            ],
                        ],
                    ],
                    //Fin subruta agregarusuario
                    //Subruta  nuevo
                    'nuevo' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevo',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'nuevo',
                            ],
                        ],
                    ],
                    //Fin subruta nuevo
                    //Subruta  cerrarsesion
                    'cerrarsesion' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/cerrarsesion',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'cerrarsesion',
                            ],
                        ],
                    ],
                    //Fin subruta cerrarsesion
                    //Subruta  cerrarsesione
                    'cerrarsesione' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/cerrarsesione',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'cerrarsesione',
                            ],
                        ],
                    ],
                    //Fin subruta cerrarsesione
                    //Subruta  autoregistro
                    'autoregistro' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/autoregistro',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'autoregistro',
                            ],
                        ],
                    ],
                    //Subruta  recupass
                    'recupass' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/recupass',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'recupass',
                            ],
                        ],
                    ],
                    //Fin subruta recupass
                    //Subruta  cambio_contrasena
                    'cambiocontrasena' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/cambiocontrasena',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'cambiocontrasena',
                            ],
                        ],
                    ],
                    //Fin subruta cambio_contrasena
                    //Subruta  contrasenia
                    'contrasenia' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/contrasenia',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'contrasenia',
                            ],
                        ],
                    ],
                    //Fin subruta contrasenia    
                    //Fin subruta autoregistro
                    //Subruta  gettablausuarios
                    'gettablausuarios' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablausuarios',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'gettablausuarios',
                            ],
                        ],
                    ],
                    //Fin subruta gettablausuarios
                    //Subruta  exportarexcelusuarios
                    'exportarexcelusuarios' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexcelusuarios',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'exportarexcelusuarios',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexcelusuarios
                    //Subruta  exportarpdfusuarios
                    'exportarpdfusuarios' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfusuarios',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'exportarpdfusuarios',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfusuarios
                    //Subruta ver
                    'ver' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/ver/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'ver',
                            ],
                        ],
                    ],
                    //Fin Subruta asignarp
                    //Subruta editar
                    'editar' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editar/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'editar',
                            ],
                        ],
                    ],
                    //Fin Subruta editar
                    //Subruta eliminar
                    'eliminar' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminar/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'eliminar',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminar
                    //Subruta  correo
                    'correo' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/correo',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'correo',
                            ],
                        ],
                    ],
                    //Fin subruta correo
                    //Subruta  contrasena
                    'contrasena' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/contrasena',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'contrasena',
                            ],
                        ],
                    ],
                    //Fin subruta contrasena
                    //Subruta  guardarrespuestaasync
                    'guardarrespuestaasync' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/guardarrespuestaasync',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'guardarrespuestaasync',
                            ],
                        ],
                    ],
                    //Fin subruta guardarrespuestaasync
                    //Subruta  eliminarrespuestaasync
                    'eliminarrespuestaasync' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminarrespuestaasync',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'eliminarrespuestaasync',
                            ],
                        ],
                    ],
                    //Fin subruta eliminarrespuestaasync
                    //Subruta  recpassmail
                    'recpassmail' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/recpassmail',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'recpassmail',
                            ],
                        ],
                    ],
                    //Fin subruta recpassmail
                    //Subruta  cambiocorreo
                    'cambiocorreo' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/cambiocorreo',
                            'defaults' => [
                                'controller' => 'Application\Controller\Usuarios',
                                'action' => 'cambiocorreo',
                            ],
                        ],
                    ],
                    //Fin subruta cambiocorreo
                    //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador Usuarios
            //Subruta  correo
            'correos' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/correos',
                    'defaults' => [
                        'controller' => 'Application\Controller\Correos',
                        'action' => 'correos',
                    ],
                ],
            ],
            //Fin subruta correo
            //Ruta para el controlador mantenimientos
            'mantenimientos' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/mantenimientos',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Mantenimientos',
                        'action' => 'mantenimientos',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //SUB RUTAS
                    //Subruta  tipodato
                    'tipodato' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/tipodato',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'tipodato',
                            ],
                        ],
                    ],
                    
                    //Subruta  exportarexceltipodato
                    'exportarexceltipodato' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexceltipodato',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarexceltipodato',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexceltipodato
                    //Subruta  exportarpdftipodato
                    'exportarpdftipodato' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/tipodato',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'tipodato',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdftipodato
                    //Subruta  editartipodato
                    'editartipodato' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editartipodato',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editartipodato',
                            ],
                        ],
                    ],
                    //Fin subruta editartipodato
                    //Subruta  getinfotipodato
                    'getinfotipodato' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfotipodato',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfotipodato',
                            ],
                        ],
                    ],
                    //Fin subruta getinfotipodato
                    //Subruta eliminartipodato
                    'eliminartipodato' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminartipodato/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminartipodato',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminartipodato
                    //Subruta  gettablatipodato
                    'gettablatipodato' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablatipodato',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettablatipodato',
                            ],
                        ],
                    ],
                    //Subruta  nuevotipoDATO
                    'nuevotipodato' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevotipodato',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'nuevotipodato',
                            ],
                        ],
                    ],
                    //Fin subruta nuevo tipo dato
                    //Subruta  nuevotiposoli
                    'nuevotiposoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevotiposoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'nuevadependencia',
                            ],
                        ],
                    ],

                    //Subruta  tiposoli1
                    'tiposoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/tiposoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'tiposoli',
                            ],
                        ],
                    ],

                    //Subruta  firmasello
                    'firmasello' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/firmasello',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'firmasello',
                            ],
                        ],
                    ],
                    
                    //Subruta  guardarFirmaSello
                    'guardarFirmaSello' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/guardar-firma-sello',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'guardarFirmaSello',
                            ],
                        ],
                    ],
                    
                    //Subruta  exportarexceltiposoli
                    'exportarexceltiposoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexceltiposoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarexceltiposoli',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexceldependencias
                    //Subruta  exportarpdftiposoli
                    'exportarpdftiposoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/tiposoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'tiposoli',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfdependencias
                    //Subruta  editartiposoli
                    'editartiposoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editartiposoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editartiposoli',
                            ],
                        ],
                    ],
                    //Fin subruta editardependencia
                    //Subruta  getinfotiposoli
                    'getinfotiposoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfotiposoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfotiposoli',
                            ],
                        ],
                    ],
                    //Fin subruta getinfodependencia
                    //Subruta eliminartiposoli
                    'eliminartiposoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminartiposoli/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminartiposoli',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminartiposoli
                    //Subruta  gettablatiposoli
                    'gettablatiposoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablatiposoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettablatiposoli',
                            ],
                        ],
                    ],
                    //Fin subruta gettablatiposoli
                    //Subruta  nuevotiposoli
                    'nuevotiposoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevotiposoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'nuevadependencia',
                            ],
                        ],
                    ],
                    //Fin sub ruta nuevo tipo de solicitud
                    //Subruta  tipodato
                    'estadosoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/estadosoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'estadosoli',
                            ],
                        ],
                    ],
                     //Subruta  nuevoestadosoli
                    'nuevoestadosoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevoestadosoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'nuevaestadosoli',
                            ],
                        ],
                    ],
                    //Subruta  gettblestadosolicitud
                    'gettablaestadosoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablaestadosoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettablaestadosoli',
                            ],
                        ],
                    ],
                    //Fin subruta gettblestadosolicitud
                    //Subruta  getinfoestadosolicitud
                    'getinfoestadosoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfoestadosoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfoestadosoli',
                            ],
                        ],
                    ],
                    //Fin subruta getinfoestadosolicitud
                    //Subruta  editarestadosolicitud
                    'editarestadosoli' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarestadosoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editarestadosoli',
                            ],
                        ],
                    ],
                    //Fin subruta editarestadosolicitud
                    //Subruta eliminarestadosolicitud
                    'eliminarestadosolid' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminarestadosoli/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminarestadosoli',
                            ],
                        ],
                    ],
                    //Fin subruta nuevatiposoli
                   //Subruta  Categoriavalor
                    'categoriavalor' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/categoriavalor',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'categoriavalor',
                            ],
                        ],
                    ],
                    
                    //Subruta  exportarexcelcategoriavalor
                    'exportarexcelcategoriavalor' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexcelcategoriavalor',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarexcelcategoriavalor',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexcelcategoriavalor
                    //Subruta  exportarpdfcategoriavalor
                    'exportarpdfticategoriavalorpodato' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/categoriavalor',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'categoriavalor',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfcategoriavalor
                    //Subruta  editarcategoriavalor
                    'editarcategoriavalor' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarcategoriavalor',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editarcategoriavalor',
                            ],
                        ],
                    ],
                    //Fin subruta editarcategoriavalor
                    //Subruta  getinfocategoriavalor
                    'getinfocategoriavalor' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfocategoriavalor',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfocategoriavalor',
                            ],
                        ],
                    ],
                    //Fin subruta getinfocategoriavalor
                    //Subruta eliminarcategoriavalor
                    'eliminarcategoriavalor' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminarcategoriavalor/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminarcategoriavalor',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminarcategoriavalor
                    //Subruta  gettablacategoriavalor
                    'gettablacategoriavalor' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablacategoriavalor',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettablacategoriavalor',
                            ],
                        ],
                    ],
                    //Subruta  nuevocategoriavalor
                    'nuevocategoriavalor' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevocategoriavalor',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'nuevocategoriavalor',
                            ],
                        ],
                    ],
                    //Subruta  rangos
                    'rangos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/rangos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'rangos',
                            ],
                        ],
                    ],
                    
                    //Subruta  dependencias
                    'dependencias' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/dependencias',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'dependencias',
                            ],
                        ],
                    ],
                    //Fin subruta dependencias//
                    //Subruta  exportarexceldependencias
                    'exportarexceldependencias' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexceldependencias',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarexceldependencias',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexceldependencias
                    //Subruta  exportarpdfdependencias
                    'exportarpdfdependencias' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfdependencias',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarpdfdependencias',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfdependencias
                    //Subruta  editardependencia
                    'editardependencia' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editardependencia',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editardependencia',
                            ],
                        ],
                    ],
                    //Fin subruta editardependencia
                    //Subruta  getinfodependencia
                    'getinfodependencia' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfodependencia',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfodependencia',
                            ],
                        ],
                    ],
                    //Fin subruta getinfodependencia
                    //Subruta eliminardependencia
                    'eliminardependencia' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminardependencia/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminardependencia',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminardependencia
                    //Subruta  gettabladependencias
                    'gettabladependencias' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettabladependencias',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettabladependencias',
                            ],
                        ],
                    ],
                    //Fin subruta gettabladependencias
                    //Subruta  nuevadependencia
                    'nuevadependencia' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevadependencia',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'nuevadependencia',
                            ],
                        ],
                    ],
                    //Fin subruta nuevadependencia
                    //Subruta  rangos
                    'rangos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/rangos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'rangos',
                            ],
                        ],
                    ],
                    //Fin subruta rangos
                    //Subruta  exportarexcelrangos
                    'exportarexcelrangos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexcelrangos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarexcelrangos',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexcelrangos
                    //Subruta  exportarpdfrangos
                    'exportarpdfrangos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfrangos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarpdfrangos',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfrangos
                    //Subruta  getinforango
                    'getinforango' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinforango',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinforango',
                            ],
                        ],
                    ],
                    //Fin subruta getinforango
                    //Subruta eliminarrango
                    'eliminarrango' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminarrango/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminarrango',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminarrango
                    //Subruta  gettablarangos
                    'gettablarangos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablarangos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettablarangos',
                            ],
                        ],
                    ],
                    //Fin subruta gettablarangos
                    //Subruta  nuevorango
                    'nuevorango' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevorango',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'nuevorango',
                            ],
                        ],
                    ],
                    //Fin subruta nuevorango
                    //Subruta  editarrango
                    'editarrango' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarrango',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editarrango',
                            ],
                        ],
                    ],
                    //Fin subruta editarrango
                    //Subruta  categorias
                    'categorias' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/categorias',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'categorias',
                            ],
                        ],
                    ],
                    //Fin subruta categorias
                    //Subruta  exportarexcelcategorias
                    'exportarexcelcategorias' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexcelcategorias',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarexcelcategorias',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexcelcategorias
                    //Subruta  exportarpdfcategorias
                    'exportarpdfcategorias' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfcategorias',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarpdfcategorias',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfcategorias
                    //Subruta  getinfocategoria
                    'getinfocategoria' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfocategoria',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfocategoria',
                            ],
                        ],
                    ],
                    //Fin subruta infocategoria
                    //Subruta  editarcategoria
                    'editarcategoria' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarcategoria',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editarcategoria',
                            ],
                        ],
                    ],
                    //Fin subruta editarcategoria
                    //Subruta eliminarcategoria
                    'eliminarcategoria' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminarcategoria/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminarcategoria',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminarcategoria
                    //Subruta  gettablacategorias
                    'gettablacategorias' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablacategorias',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettablacategorias',
                            ],
                        ],
                    ],
                    //Fin subruta gettablacategorias
                    //Subruta  nuevacategoria
                    'nuevacategoria' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevacategoria',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'nuevacategoria',
                            ],
                        ],
                    ],
                    //Fin subruta nuevacategoria
                    //Subruta  departamentos
                    'departamentos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/departamentos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'departamentos',
                            ],
                        ],
                    ],
                    //Fin subruta departamentos
                    //Subruta  exportarexceldepartamentos
                    'exportarexceldepartamentos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexceldepartamentos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarexceldepartamentos',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexceldepartamentos
                    //Subruta  exportarpdfdepartamentos
                    'exportarpdfdepartamentos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfdepartamentos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarpdfdepartamentos',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfdepartamentos
                    //Subruta  editardepartamento
                    'editardepartamento' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editardepartamento',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editardepartamento',
                            ],
                        ],
                    ],
                    //Fin subruta editardepartamento
                    //Subruta  getinfodepartamento
                    'getinfodepartamento' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfodepartamento',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfodepartamento',
                            ],
                        ],
                    ],
                    //Fin subruta getinfodepartamento
                    //Subruta eliminardepartamento
                    'eliminardepartamento' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminardepartamento/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminardepartamento',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminardepartamento
                    //Subruta  gettabladepartamentos
                    'gettabladepartamentos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettabladepartamentos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettabladepartamentos',
                            ],
                        ],
                    ],
                    //Fin subruta gettabladepartamentos
                    //Subruta  nuevodepartamento
                    'nuevodepartamento' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevodepartamento',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'nuevodepartamento',
                            ],
                        ],
                    ],
                    //Subruta  medicos
                    'medicos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/medicos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'medicos',
                            ],
                        ],
                    ],
                    //Fin subruta medicos
                    //Subruta  especialidades
                    'especialidades' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/especialidades',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'especialidades',
                            ],
                        ],
                    ],
                    //Fin subruta especialidades
                    //Subruta  exportarexcelespecialidades
                    'exportarexcelespecialidades' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexcelespecialidades',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarexcelespecialidades',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexcelespecialidades
                    //Subruta  exportarpdfespecialidades
                    'exportarpdfespecialidades' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfespecialidades',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarpdfespecialidades',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfespecialidades
                    //Subruta  getinfoespecialidad
                    'getinfoespecialidad' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfoespecialidad',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfoespecialidad',
                            ],
                        ],
                    ],
                    //Fin subruta getinfoespecialidad
                    //Subruta  editarespecialidades
                    'editarespecialidad' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarespecialidad',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editarespecialidad',
                            ],
                        ],
                    ],
                    //Fin subruta editarespecialidades
                    //Subruta eliminarespecialidades
                    'eliminarespecialidades' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminarespecialidades/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminarespecialidades',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminarespecialidades
                    //Subruta  ecivil
                    'ecivil' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/ecivil',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'ecivil',
                            ],
                        ],
                    ],
                    //Fin subruta estado_civil
                    //Subruta  exportarexcelestadoscivil
                    'exportarexcelestadoscivil' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexcelestadoscivil',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarexcelestadoscivil',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexcelestadoscivil
                    //Subruta  exportarpdfestadoscivil
                    'exportarpdfestadoscivil' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfestadoscivil',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarpdfestadoscivil',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfestadoscivil
                    //Subruta  getinfoecivil
                    'getinfoecivil' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfoecivil',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfoecivil',
                            ],
                        ],
                    ],
                    //Fin subruta getinfoecivil
                    //Subruta  editarecivil
                    'editarecivil' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarecivil',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editarecivil',
                            ],
                        ],
                    ],
                    //Fin subruta editarecivil
                    //Subruta eliminarecivil
                    'eliminarecivil' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminarecivil/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminarecivil',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminarecivil 
                    //Subruta  gettblcivil
                    'gettblcivil' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettblcivil',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettblcivil',
                            ],
                        ],
                    ],
                    //Fin subruta gettblcivil
                    //Subruta  gttblespecialidad
                    'gttblespecialidad' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gttblespecialidad',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gttblespecialidad',
                            ],
                        ],
                    ],
                    //Fin subruta gttblespecialidad
                    //Subruta  sucursales
                    'sucursales' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/sucursales',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'sucursales',
                            ],
                        ],
                    ],
                    //Fin subruta sucursales
                    //Subruta  gettblsucursales
                    'gettblsucursales' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettblsucursales',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettblsucursales',
                            ],
                        ],
                    ],
                    //Fin subruta gettblsucursales
                    //Subruta  getinfosucursal
                    'getinfosucursal' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfosucursal',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfosucursal',
                            ],
                        ],
                    ],
                    //Fin subruta getinfosucursal
                    //Subruta  editarsucursal
                    'editarsucursal' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarsucursal',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editarsucursal',
                            ],
                        ],
                    ],
                    //Fin subruta editarsucursal
                    //Subruta eliminarsucursal
                    'eliminarsucursal' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminarsucursal/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminarsucursal',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminarsucursal
                    //Subruta  estadosu
                    'estadosu' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/estadosu',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'estadosu',
                            ],
                        ],
                    ],
                    //Fin subruta estadosu
                    //Subruta  gettblestadosu
                    'gettblestadosu' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettblestadosu',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettblestadosu',
                            ],
                        ],
                    ],
                    //Fin subruta gettblestadosu
                    //Subruta  getinfoestadosu
                    'getinfoestadosu' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfoestadosu',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfoestadosu',
                            ],
                        ],
                    ],
                    //Fin subruta getinfoestadosu
                    //Subruta  editarestadosu
                    'editarestadosu' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarestadosu',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editarestadosu',
                            ],
                        ],
                    ],
                    //Fin subruta editarestadosu
                    //Subruta eliminarestadoe
                    'eliminarestadoe' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminarestadoe/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminarestadoe',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminarestadoe
                    //Subruta  tipoexamen
                    'tipoexamen' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/tipoexamen',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'tipoexamen',
                            ],
                        ],
                    ],
                    //Fin subruta tipoexamen//
                    //Subruta  exportarexceltipoexamen
                    'exportarexceltipoexamen' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexceltipoexamen',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarexceltipoexamen',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexceltipoexamen
                    //Subruta exportarpdftipoexamen
                    'exportarpdftipoexamen' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdftipoexamen',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'exportarpdftipoexamen',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdftipoexamen
                    //Subruta  editartipoexamen
                    'editartipoexamen' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editartipoexamen',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'editartipoexamen',
                            ],
                        ],
                    ],
                    //Fin subruta editartipoexamen
                    //Subruta  getinfotipoexamen
                    'getinfotipoexamen' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getinfotipoexamen',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'getinfotipoexamen',
                            ],
                        ],
                    ],
                    //Fin subruta getinfotipoexamen
                    //Subruta eliminartipoexamen
                    'eliminartipoexamen' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminartipoexamen/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'eliminartipoexamen',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminartipoexamen
                    //Subruta  gettablatipoexamen
                    'gettablatipoexamen' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablatipoexamen',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'gettablatipoexamen',
                            ],
                        ],
                    ],
                    //Fin subruta gettablatipoexamen
                    //Subruta  nuevatipoexamen
                    'nuevatipoexamen' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevatipoexamen',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'nuevatipoexamen',
                            ],
                        ],
                    ],
                //Fin subruta nuevatipoexamen
                //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador mantenimiento
            //Ruta para el controlador getnombreafiliado
            'citas' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/citas',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Citas',
                        'action' => 'citas',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //SUB RUTAS
                    //Subruta  nueva
                    'nueva' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nueva',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'nueva',
                            ],
                        ],
                    ],
                    //Fin subruta nuevo
                    //Subruta pdffichacitas
                    'pdffichacitas' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/pdffichacitas/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'pdffichacitas',
                            ],
                        ],
                    ],
                    //Fin Subruta pdffichacitas
                    //Subruta  getnombremedicoaja
                    'getnombremedicoaja' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getnombremedicoaja',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'getnombremedicoaja',
                            ],
                        ],
                    ],
                    //Subruta eliminarcita
                    'eliminarcita' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminarcita/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'eliminarcita',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminar
                    //Fin subruta getNombreMedicoaja
                    //Subruta  consulta
                    'consultaspendientes' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/consultaspendientes',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'consultaspendientes',
                            ],
                        ],
                    ],
                    //Subruta  gestionarcitas
                    'gestionarcitas' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gestionarcitas',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'gestionarcitas',
                            ],
                        ],
                    ],
                    //Fin subruta gestionarcitas
                    //Fin subruta consulta
                    //Subruta  calendario
                    'calendario' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/calendario',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'calendario',
                            ],
                        ],
                    ],
                    //Fin subruta calendario
                    //Subruta pdffichacitas
                    'pdffichacitas' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/pdffichacitas/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'pdffichacitas',
                            ],
                        ],
                    ],
                    //Fin Subruta pdffichacitas
                    //Subruta  gettblgeneralcitas
                    'gettblgeneralcitas' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettblgeneralcitas',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'gettblgeneralcitas',
                            ],
                        ],
                    ],
                    //Fin subruta gettblgeneralcitas
                    //Subruta  exportarpdfgenerlcitas
                    'exportarpdfgenerlcitas' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfgenerlcitas',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'exportarpdfgenerlcitas',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfgenerlcitas
                    //Subruta  exportarpdfgeneralcitas
                    'exportarpdfgeneralcitas' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfgeneralcitas',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'exportarpdfgeneralcitas',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfgeneralcitas
                    //Subruta  calgetcitas
                    'calgetcitas' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/calgetcitas',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'calgetcitas',
                            ],
                        ],
                    ],
                    //Fin subruta calgetcitas
                    //Subruta  caleneditarcita
                    'caleneditarcita' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/caleneditarcita',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'caleneditarcita',
                            ],
                        ],
                    ],
                    //Fin subruta caleneditarcita
                    //Subruta  calennuevacita
                    'calennuevacita' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/calennuevacita',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'calennuevacita',
                            ],
                        ],
                    ],
                    //Fin subruta calennuevacita
                    //Subruta  cambiarmedico
                    'cambiarmedico' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/cambiarmedico',
                            'defaults' => [
                                'controller' => 'Application\Controller\Citas',
                                'action' => 'cambiarmedico',
                            ],
                        ],
                    ],
                //Fin subruta cambiarmedico
                //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador citas 
            //Ruta para el controlador Bitacoras
            'Bitacoras' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/bitacoras',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Bitacoras',
                        'action' => 'bitacoras',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //SUB RUTAS
                    //Subruta  gettablabitacora
                    'gettablabitacora' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablabitacora',
                            'defaults' => [
                                'controller' => 'Application\Controller\Bitacoras',
                                'action' => 'gettablabitacora',
                            ],
                        ],
                    ],
                    //Fin subruta gettablabitacora
                    //Subruta gettablabitacoraf
                    'gettablabitacoraf' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablabitacoraf/:fi/:ff',
                            //     Aqui los contraints
                            //    'constraints' => [
                            //        'fi/:ff' => '[0-9]+', //Expresión regular que acepta
                            //    ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Bitacoras',
                                'action' => 'gettablabitacoraf',
                            ],
                        ],
                    ],
                    //Fin Subruta gettablabitacoraf
                    //Subruta  exportarexcelbitacora
                    'exportarexcelbitacora' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarexcelbitacora',
                            'defaults' => [
                                'controller' => 'Application\Controller\Bitacoras',
                                'action' => 'exportarexcelbitacora',
                            ],
                        ],
                    ],
                    //Fin subruta exportarexcelbitacora
                    //Subruta  exportarpdfbitacora
                    'exportarpdfbitacora' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfbitacora',
                            'defaults' => [
                                'controller' => 'Application\Controller\Bitacoras',
                                'action' => 'exportarpdfbitacora',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfbitacora
                    //Subruta  validarintegridad
                    'validarintegridad' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/validarintegridad',
                            'defaults' => [
                                'controller' => 'Application\Controller\Bitacoras',
                                'action' => 'validarintegridad',
                            ],
                        ],
                    ],
                //Fin subruta validarintegridad
                //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador Bitacora
            //Ruta para el controlador respaldos
            'respaldos' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/respaldos',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Respaldos',
                        'action' => 'backup',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //SUB RUTAS
                    //Subruta  backup
                    'backup' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/backup',
                            'defaults' => [
                                'controller' => 'Application\Controller\Respaldos',
                                'action' => 'backup',
                            ],
                        ],
                    ],
                    //Fin subruta backup
                    //Subruta  restaurararchivo
                    'restaurararchivo' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/restaurararchivo',
                            'defaults' => [
                                'controller' => 'Application\Controller\Respaldos',
                                'action' => 'restaurararchivo',
                            ],
                        ],
                    ],
                //Fin subruta restaurararchivo
                //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador respaldos
            // 
            //Ruta para el controlador Roles
            'Roles' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/roles',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Roles',
                        'action' => 'roles',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //SUB RUTAS
                    //Subruta  nuevo
                    'nuevo' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevo',
                            'defaults' => [
                                'controller' => 'Application\Controller\Roles',
                                'action' => 'nuevo',
                            ],
                        ],
                    ],
                    //Fin subruta nuevo
                    //Subruta  gettablaroles
                    'gettablaroles' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablaroles',
                            'defaults' => [
                                'controller' => 'Application\Controller\Roles',
                                'action' => 'gettablaroles',
                            ],
                        ],
                    ],
                    //Fin subruta gettablaroles
                    //Subruta  exportarroles
                    'exportarroles' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarroles',
                            'defaults' => [
                                'controller' => 'Application\Controller\Roles',
                                'action' => 'exportarroles',
                            ],
                        ],
                    ],
                    //Fin subruta exportarroles
                    //Subruta editar
                    'editar' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editar/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Roles',
                                'action' => 'editar',
                            ],
                        ],
                    ],
                    //Fin Subruta editar
                    //Subruta eliminar
                    'eliminar' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminar/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Roles',
                                'action' => 'eliminar',
                            ],
                        ],
                    ],
                //Fin Subruta eliminar
                //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador Roles
            //Ruta para el controlador Permisos
            'permisos' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/permisos',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Permisos',
                        'action' => 'permisos',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //SUB RUTAS
                    //Subruta  nuevo
                    'nuevo' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevo',
                            'defaults' => [
                                'controller' => 'Application\Controller\Permisos',
                                'action' => 'nuevo',
                            ],
                        ],
                    ],
                    //Fin subruta nuevo
                    //Subruta asinaciondepermisos
                    'asinaciondepermisos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/asinaciondepermisos/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Permisos',
                                'action' => 'asinaciondepermisos',
                            ],
                        ],
                    ],
                    //Fin Subruta asinaciondepermisos 
                    //Subruta borrarset
                    'borrarset' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/borrarset/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Permisos',
                                'action' => 'borrarset',
                            ],
                        ],
                    ],
                    //Fin Subruta borrarset
                    //Subruta  validarpermisousuario
                    'validarpermisousuario' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/validarpermisousuario',
                            'defaults' => [
                                'controller' => 'Application\Controller\Permisos',
                                'action' => 'validarpermisousuario',
                            ],
                        ],
                    ],
                    //Fin subruta validarpermisousuario
                    //Subruta  gttblpermisos
                    'gttblpermisos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gttblpermisos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Permisos',
                                'action' => 'gttblpermisos',
                            ],
                        ],
                    ],
                    //Fin subruta gttblpermisos
                    //Subruta  exportarpdfpermisos
                    'exportarpdfpermisos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfpermisos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Permisos',
                                'action' => 'exportarpdfpermisos',
                            ],
                        ],
                    ],
                //Fin subruta exportarpdfpermisos
                //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador Permisos
            //Ruta para el controlador Parametros
            'parametros' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/parametros',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Parametros',
                        'action' => 'parametros',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //Subruta  nuevo
                    'nuevo' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevo',
                            'defaults' => [
                                'controller' => 'Application\Controller\Parametros',
                                'action' => 'nuevo',
                            ],
                        ],
                    ],
                    //Fin subruta nuevo
                    //Subruta editar
                    'editar' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editar/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Parametros',
                                'action' => 'editar',
                            ],
                        ],
                    ],
                    //Fin Subruta editar
                    //Subruta eliminar
                    'eliminar' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminar/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Parametros',
                                'action' => 'eliminar',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminar
                    //Subruta  gettablaparametros
                    'gettablaparametros' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablaparametros',
                            'defaults' => [
                                'controller' => 'Application\Controller\Parametros',
                                'action' => 'gettablaparametros',
                            ],
                        ],
                    ],
                    //Fin subruta gettablaparametros
                    //Subruta  exportarpdfparametros
                    'exportarpdfparametros' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfparametros',
                            'defaults' => [
                                'controller' => 'Application\Controller\Parametros',
                                'action' => 'exportarpdfparametros',
                            ],
                        ],
                    ],
                //Fin subruta exportarpdfparametros
                //
                //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador Parametros
            //Ruta para el controlador Objetos
            'objetos' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/objetos',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Objetos',
                        'action' => 'objetos',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //SUB RUTAS
                    //Subruta  nuevo
                    'nuevo' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/nuevo',
                            'defaults' => [
                                'controller' => 'Application\Controller\Objetos',
                                'action' => 'nuevo',
                            ],
                        ],
                    ],
                    //Fin subruta nuevo
                    //Subruta  gettablaobjetos
                    'gettablaobjetos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablaobjetos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Objetos',
                                'action' => 'gettablaobjetos',
                            ],
                        ],
                    ],
                    //Fin subruta gettablaobjetos
                    //Subruta  exportarpdfobjetos
                    'exportarpdfobjetos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfobjetos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Objetos',
                                'action' => 'exportarpdfobjetos',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfobjetos
                    //Subruta editar
                    'editar' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editar/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Objetos',
                                'action' => 'editar',
                            ],
                        ],
                    ],
                    //Fin Subruta editar
                    //Subruta eliminar
                    'eliminar' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminar/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Objetos',
                                'action' => 'eliminar',
                            ],
                        ],
                    ],
                //Fin Subruta eliminar
                //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador Objetos
            //Ruta para el controlador Pacientes
            'pacientes' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/pacientes',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Pacientes',
                        'action' => 'pacientes',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //SUB RUTAS
                    //Subruta preclinica
                    'preclinica' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/preclinica/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'preclinica',
                            ],
                        ],
                    ],
                    //Fin Subruta preclinica
                    //Subruta editarpreclinica
                    'editarpreclinica' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarpreclinica/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'editarpreclinica',
                            ],
                        ],
                    ],
                    //Fin Subruta editarpreclinica
                    //Subruta gettblhistoricopaciente
                    'gettblhistoricopaciente' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettblhistoricopaciente/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'gettblhistoricopaciente',
                            ],
                        ],
                    ],
                    //Fin Subruta gettblhistoricopaciente
                    //Subruta gettblhistoricopacientea
                    'gettblhistoricopacientea' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettblhistoricopacientea/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'gettblhistoricopacientea',
                            ],
                        ],
                    ],
                    //Fin Subruta gettblhistoricopaciente
                    //Subruta  gettblallpacientes
                    'gettblallpacientes' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettblallpacientes',
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'gettblallpacientes',
                            ],
                        ],
                    ],
                    //Fin subruta gettblallpacientes
                    //Subruta expedientehistorico
                    'expedientehistorico' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/expedientehistorico/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'expedientehistorico',
                            ],
                        ],
                    ],
                    //Fin Subruta expedientehistorico 
                    //Subruta expedientehistoricoa
                    'expedientehistoricoa' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/expedientehistoricoa/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'expedientehistoricoa',
                            ],
                        ],
                    ],
                    //Fin Subruta expedientehistoricoa
                    //Subruta verexpedientemedico
                    'verexpedientemedico' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/verexpedientemedico/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'verexpedientemedico',
                            ],
                        ],
                    ],
                    //Fin Subruta verexpedientemedico
                    //Subruta verexpedientemedicoa
                    'verexpedientemedicoa' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/verexpedientemedicoa/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'verexpedientemedicoa',
                            ],
                        ],
                    ],
                    //Fin Subruta verexpedientemedicoa
                    //Subruta  consultas
                    'consultas' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/consultas',
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'consultas',
                            ],
                        ],
                    ],
                    //Subruta  historicopacientes
                    'historicopacientes' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/historicopacientes',
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'historicopacientes',
                            ],
                        ],
                    ],
                    //Fin subruta historicopacientes
                    //Fin subruta consultas
                    //Subruta  gettablaexpediente
                    'gettablaexpediente' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablaexpediente',
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'gettablaexpediente',
                            ],
                        ],
                    ],
                    //Fin subruta gettablaexpediente
                    //Subruta  triaje
                    'triaje' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/triaje',
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'triaje',
                            ],
                        ],
                    ],
                    //Fin subruta trieaje
                    //Subruta  gettablapacientes
                    'gettablapacientes' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablapacientes',
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'gettablapacientes',
                            ],
                        ],
                    ],
                    //Fin subruta gettablapacientes
                    //Subruta expedientemedico
                    'expedientemedico' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/expedientemedico/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'expedientemedico',
                            ],
                        ],
                    ],
                    //Fin Subruta expedientemedico
                    //Subruta  exportexceltriaje
                    'exportexceltriaje' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportexceltriaje',
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'exportexceltriaje',
                            ],
                        ],
                    ],
                    //Fin subruta exportexceltriaje
                    //Subruta  exportpdftriaje
                    'exportpdftriaje' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportpdftriaje',
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'exportpdftriaje',
                            ],
                        ],
                    ],
                    //Fin subruta exportpdftriaje
                    //Subruta  histoexpedienteExcel
                    'histoexpedienteExcel' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/histoexpedienteExcel',
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'histoexpedienteExcel',
                            ],
                        ],
                    ],
                    //Fin subruta histoexpedienteExcel
                    //Subruta  histoexpedientepdf
                    'histoexpedientepdf' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/histoexpedientepdf',
                            'defaults' => [
                                'controller' => 'Application\Controller\Pacientes',
                                'action' => 'histoexpedientepdf',
                            ],
                        ],
                    ],
                //Fin subruta histoexpedientepdf               
                //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador Pacientes
            //Ruta para el controlador Afiliados
            'afiliados' => [
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => [
                    'route' => '/afiliados',
                    'defaults' => [
                        '__NAMESPACE__' => 'Application\Controller',
                        'controller' => 'Afiliados',
                        'action' => 'afiliados',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    //SUB RUTAS
                    //Subruta  agregarafiliado
                    'agregarafiliado' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/agregarafiliado',
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'agregarafiliado',
                            ],
                        ],
                    ],
                    //Fin subruta agregarafiliado
                    //Subruta  registrardoctor
                    'registrardoctor' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/registrardoctor',
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'registrardoctor',
                            ],
                        ],
                    ],
                    //Fin subruta registrardoctor
                    //Subruta eliminarafiliado
                    'eliminarafiliado' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/eliminarafiliado/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'eliminarafiliado',
                            ],
                        ],
                    ],
                    //Fin Subruta eliminarafiliado
                    //Subruta agregarbeneficiario
                    'agregarbeneficiario' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/agregarbeneficiario/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'agregarbeneficiario',
                            ],
                        ],
                    ],
                    //Fin Subruta agregarbeneficiario
                    //Subruta verbeneficiarios
                    'verbeneficiarios' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/verbeneficiarios/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'verbeneficiarios',
                            ],
                        ],
                    ],
                    //Fin Subruta verbeneficiarios
                    //Subruta gettblbeneficiarios
                    'gettblbeneficiarios' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettblbeneficiarios/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'gettblbeneficiarios',
                            ],
                        ],
                    ],
                    //Fin Subruta gettblbeneficiarios
                    //Subruta editar
                    'editar' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editar/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'editar',
                            ],
                        ],
                    ],
                    //Fin Subruta editar
                    //Subruta editarbeneficiario
                    'editarbeneficiario' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarbeneficiario/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'editarbeneficiario',
                            ],
                        ],
                    ],
                    //Fin Subruta editarbeneficiario
                    //Fin Subruta agregarbeneficiario
                    //Subruta  reporte
                    'reporte' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/reporte',
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'reporte',
                            ],
                        ],
                    ],
                    //Fin subruta reporte
                    //Subruta  gettablaafiliados
                    'gettablaafiliados' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettablaafiliados',
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'gettablaafiliados',
                            ],
                        ],
                    ],
                    //Fin subruta gettablaafiliados
                    //Subruta agregarbeneficiario
                    'agregarbeneficiario' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/agregarbeneficiario/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'agregarbeneficiario',
                            ],
                        ],
                    ],
                    //Fin Subruta agregarbenficiario           
                    //Subruta  getnombrepaciente
                    'getnombrepaciente' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getnombrepaciente',
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'getnombrepaciente',
                            ],
                        ],
                    ],
                    //Fin subruta getnombrepaciente
                    //Subruta  getnombreafiliado
                    'getnombreafiliado' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/getnombreafiliado',
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'getnombreafiliado',
                            ],
                        ],
                    ],
                    //Fin subruta getnombreafiliado
                    //Subruta pdffichaafiliado
                    'pdffichaafiliado' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/pdffichaafiliado/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'pdffichaafiliado',
                            ],
                        ],
                    ],
                    //SUB RUTAS
                    //Subruta  tiposoli
                    'dependencias' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/tiposoli',
                            'defaults' => [
                                'controller' => 'Application\Controller\Mantenimientos',
                                'action' => 'tiposoli',
                            ],
                        ],
                    ],
                    //Fin Subruta pdffichaafiliado
                    //Subruta  listmedicos
                    'listmedicos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/listmedicos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'listmedicos',
                            ],
                        ],
                    ],
                    //Fin subruta listmedicos
                    //Subruta  gettblListMedicos
                    'gettblListMedicos' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/gettblListMedicos',
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'gettblListMedicos',
                            ],
                        ],
                    ],
                    //Fin subruta gettblListMedicos
                    //Subruta  infomedico
                    'infomedico' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/infomedico',
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'infomedico',
                            ],
                        ],
                    ],
                    //Fin subruta infomedico
                    //Subruta  editarmedico
                    'editarmedico' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarmedico',
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'editarmedico',
                            ],
                        ],
                    ],
                    //Fin subruta editarmedico
                    //Subruta editar
                    'editarafiliado' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/editarafiliado/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'editarafiliado',
                            ],
                        ],
                    ],
                    //Fin Subruta editar
                    //Subruta cambiarestadoafiliado
                    'cambiarestadoafiliado' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/cambiarestadoafiliado/:id',
                            //Aqui los contraints
                            'constraints' => [
                                'id' => '[0-9]+', //Expresión regular que acepta
                            ],
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'cambiarestadoafiliado',
                            ],
                        ],
                    ],
                    //Fin Subruta cambiarestadoafiliado
                    //Subruta  exportarpdfafiliados
                    'exportarpdfafiliados' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/exportarpdfafiliados',
                            'defaults' => [
                                'controller' => 'Application\Controller\Afiliados',
                                'action' => 'exportarpdfafiliados',
                            ],
                        ],
                    ],
                    //Fin subruta exportarpdfafiliados
                    //FIN SUBRUTAS
                ],
            ], //Fin ruta del controlador Afiliados 
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
            'Zend\Db\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'es_ES',
        'translation_file_patterns' => array(
            array(
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
            'Application\Controller\Afiliados' => 'Application\Controller\AfiliadosController',
            'Application\Controller\Usuarios' => 'Application\Controller\UsuariosController',
            'Application\Controller\Objetos' => 'Application\Controller\ObjetosController',
            'Application\Controller\Bitacoras' => 'Application\Controller\BitacorasController',
            'Application\Controller\Roles' => 'Application\Controller\RolesController',
            'Application\Controller\Permisos' => 'Application\Controller\PermisosController',
            'Application\Controller\Parametros' => 'Application\Controller\ParametrosController',
            'Application\Controller\Pacientes' => 'Application\Controller\PacientesController',
            'Application\Controller\Citas' => 'Application\Controller\CitasController',
            'Application\Controller\Mantenimientos' => 'Application\Controller\MantenimientosController',
            'Application\Controller\Respaldos' => 'Application\Controller\RespaldosController',
            'Application\Controller\Reportes' => 'Application\Controller\ReportesController',
            'Application\Controller\Correos' => 'Application\Controller\CorreosController',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => array(
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
