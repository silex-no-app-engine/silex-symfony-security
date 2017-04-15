<?php

require __DIR__ . '/../bootstrap.php';

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();

$app['debug'] = true;

$app->get('/', function(){
    return "Nova Série - Silex com Symfony Security!";
});

$app->get('/admin', function(Application $app){
    $token = $app['security.token_storage']->getToken();
    if (null == $token){
    }
    return $app['twig']->render('admin/index.twig', ['user' => $token->getUser()]);
});

$app->get('/login', function(Application $app, Request $request){
    return $app['twig']->render('auth/login.twig',[
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
        ]);
})
->bind('login');

$app->get('/create/user', function(Application $app){
    $user = new \CodeExperts\App\Entity\User();
    $user->setName("Nanderson Castro");
    $user->setEmail("nandokstro@gmail.com");
    $user->setPassword($app['encode_password']($user, '123456'));
    $user->setRoles('ROLE_USER');
    $user->setCreateAt(new DateTime("now", new DateTimeZone("America/Belem")));
    $user->setUpdatedAt(new DateTime("now", new DateTimeZone("America/Belem")));

    $app['orm.em']->persist($user);
    $app['orm.em']->flush();

    return "Nova Série - Silex com Symfony Security!";
});

$app->register(new \Silex\Provider\TwigServiceProvider(), [
    'twig.path' => __DIR__ . '/../views'
]);
$app->register(new \Silex\Provider\SessionServiceProvider());


$app['user_repository'] = function($app) {
    return $app['orm.em']->getRepository('CodeExperts\App\Entity\User');
};

$app['encode_password'] = $app->protect(function($user, $password) use ($app){
    $encoder = $app['security.encoder_factory']->getEncoder($user);
    return $encoder->encodePassword($password, $user->getSalt());
});

$app->register(new \Silex\Provider\SecurityServiceProvider(),[
    'security.firewalls' => [
        'admin' => [
            'anonymous' => true,
            'pattern' => '^/admin',
            'form' => array('login_path' => '/login', 'check_path' => '/admin/login_check'),
            'users' => function() use($app) {
                return $app['user_repository'];
            },
            'logout' => array('logout_path' => '/admin/logout'),
        ]
    ]
]);

$app['security.access_rules'] = array(
    array('^/admin', 'ROLE_ADMIN')
);

$app->register(new \Silex\Provider\DoctrineServiceProvider(), array(
    'dbs.options' => array(
        'default' => $dbParams
    )
));
$app->register(new \Dflydev\Provider\DoctrineOrm\DoctrineOrmServiceProvider(), array(
    'orm.proxies_dir' => '/tmp',
    'orm.em.options' => array(
        'mappings' => array(
            array(
                'type' => 'annotation',
                'use_simple_annotation_reader' => false,
                'namespace' => 'CodeExperts\App\Entity',
                'path' => __DIR__ . '/src'
            ),
        ),
    ),
    'orm.proxies_namespace' => 'EntityProxy',
    'orm.auto_generate_proxies' => true,
    'orm.default_cache' => 'array'
));

$app->run();