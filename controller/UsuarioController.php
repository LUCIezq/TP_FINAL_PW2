<?php
class UsuarioController
{
    private UsuarioDao $usuarioDao;
    private MustacheRenderer $mustacheRenderer;

    public function __construct(
        UsuarioDao $usuarioDao,
        MustacheRenderer $mustacheRenderer
    ) {
        $this->usuarioDao = $usuarioDao;
        $this->mustacheRenderer = $mustacheRenderer;
    }

    public function perfil()
    {

        if (!IsLogged::isLogged()) {
            header("location: /login/index");
            exit();
        }

        $id = trim($_GET['id'] ?? '');

        if (empty($id) || !is_numeric($id)) {
            header('location: /home/index');
            exit();
        }

        $usuario = $this->usuarioDao->findById($id);

        if (empty($usuario)) {
            header('location: /home/index');
            exit();
        }

        // Metodo para visualizar datos(comentar)
        ShowData::show($usuario);


        //descomentar para renderizar la vista con los datos
        // $this->mustacheRenderer->render(
        //     "perfilUsuario",
        //     [
        //         "usuario" => $usuario
        //     ]
        // );

    }
}