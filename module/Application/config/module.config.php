<?php

declare(strict_types=1);

namespace Application;

use Application\Command\RunTaskCommand;
use Application\Factory\RecipientControllerFactory;
use Application\Factory\RecipientTableFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'recipients' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/recipients[/:id]',
                    'defaults' => [
                        'controller' => Controller\RecipientController::class,
                    ],
                    'constraints' => [
                        'id' => '[0-9]+',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'simple' => [
                'type'    => \Laminas\Router\Http\Literal::class,
                'options' => [
                    'route'    => '/simple/async-process',
                    'defaults' => [
                        'controller' => Controller\SMSController::class,
                        'action'     => 'asyncProcess',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'post' => [
                        'type' => 'Segment',
                        'options' => [
                            'route' => '/[:action]',
                            'defaults' => [
                                'action' => 'asyncProcess',
                            ],
                        ],
                        'constraints' => [
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        ],
                    ],
                ],
            ],

        ],
    ],

    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
            Controller\RecipientController::class => RecipientControllerFactory::class,
            Controller\SMSController::class => InvokableFactory::class,
        ],
    ],

    'laminas-cli' => [
        'commands' => [
            'run-task' => \Application\Command\RunTaskCommand::class,
        ],
    ],

    // Register the RecipientTable factory in service_manager, not controllers
    'service_manager' => [
        'factories' => [
            Model\RecipientTable::class => RecipientTableFactory::class,
        ],
    ],

    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],

        // Ensure this part is added for handling JSON responses
        'strategies' => [
            'ViewJsonStrategy',  // Add this strategy to handle JSON responses
        ],
    ],

];
