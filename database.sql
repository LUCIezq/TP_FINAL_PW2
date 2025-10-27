create database if not exists preguntados;

use preguntados;

DROP TABLE IF EXISTS direccion;

DROP TABLE IF EXISTS usuario;

DROP TABLE IF EXISTS nivel;

DROP TABLE IF EXISTS rol;

DROP TABLE IF EXISTS sexo;

create table sexo (
    id int auto_increment primary key,
    nombre varchar(50) not null
);

insert into
    sexo (nombre)
values
    ('Masculino'),
    ('Femenino'),
    ('Prefiero no cargarlo');

create table rol (
    id int auto_increment primary key,
    tipo varchar(40) not null
);

insert into
    rol (tipo)
values
    ('Jugador'),
    ('Administrador'),
    ('Editor');

create table nivel (
    id int auto_increment primary key,
    nivel int not null,
    experiencia_necesaria bigint not null
);

insert into
    nivel (nivel, experiencia_necesaria)
values
    (1, 0),
    (2, 100),
    (3, 250),
    (4, 450),
    (5, 700),
    (6, 1000),
    (7, 1350),
    (8, 1750),
    (9, 2200),
    (10, 2700),
    (11, 3250),
    (12, 3850),
    (13, 4500),
    (14, 5200),
    (15, 5950),
    (16, 6750),
    (17, 7600),
    (18, 8500),
    (19, 9450),
    (20, 10450);

create table usuario (
    id int auto_increment primary key,
    nombre varchar(100),
    apellido varchar(100),
    fecha_nacimiento date,
    email varchar(150) not null unique,
    contrasena varchar(255) not null,
    nombre_usuario varchar(50) not null unique,
    foto_perfil varchar(255) default null,
    experiencia int default 0,
    token_verificacion varchar(255) default null,
    verificado boolean default false,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    token_expiracion TIMESTAMP NOT NULL DEFAULT (CURRENT_TIMESTAMP + INTERVAL 24 HOUR),
    sexo_id int,
    constraint fk_usuario_sexo foreign key (sexo_id) references sexo (id),
    rol_id int,
    constraint fk_usuario_rol foreign key (rol_id) references rol (id),
    nivel_id int,
    constraint fk_usuario_nivel foreign key (nivel_id) references nivel (id)
);

insert into
    usuario (
        email,
        contrasena,
        nombre_usuario,
        foto_perfil,
        verificado,
        rol_id
    )
values
    (
        'admin@admin.com',
        'adminadmin',
        'admin',
        "/uploads/default/default.png",
        1,
        2
    ),
    (
        'editor@editor.com',
        'editoeditor',
        'editor',
        "/uploads/default/default.png",
        1,
        3
    );

create index idx_usuario_email on usuario (email);

create index idx_usuario_nombre_usuario on usuario (nombre_usuario);

create index idx_usuario_sexo_fk on usuario (sexo_id);

create index idx_usuario_rol_fk on usuario (rol_id);

create index idx_usuario_nivel_fk on usuario (nivel_id);

create table direccion (
    id int auto_increment primary key,
    calle varchar(50) not null,
    numero int not null,
    pais varchar(50) not null,
    ciudad varchar(50) not null,
    cp int not null,
    usuario_id int,
    constraint fk_direccion_usuario foreign key (usuario_id) references usuario (id) on delete cascade on update cascade
);

select
    *
from
    sexo;