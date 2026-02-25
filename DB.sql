CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50) NOT NULL,
    contraseña_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'entrenador', 'analista') NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE temporadas (
    id_temporada INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    activa TINYINT(1) DEFAULT 0
);

CREATE TABLE posiciones (
    codigo_posicion VARCHAR(10) PRIMARY KEY,
    nombre_posicion VARCHAR(50) NOT NULL,
    linea ENUM('defensa', 'medio', 'ataque') NOT NULL
);

CREATE TABLE jugadores (
    id_jugador INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    alias VARCHAR(50),
    posicion_habitual VARCHAR(10),
    estado ENUM('activo', 'lesionado', 'baja') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE dorsales_jugador (
    id_dorsal INT AUTO_INCREMENT PRIMARY KEY,
    id_jugador INT,
    id_temporada INT,
    dorsal INT,
    FOREIGN KEY (id_jugador) REFERENCES jugadores(id_jugador),
    FOREIGN KEY (id_temporada) REFERENCES temporadas(id_temporada)
);

CREATE TABLE partidos (
    id_partido INT AUTO_INCREMENT PRIMARY KEY,
    id_temporada INT,
    fecha DATETIME NOT NULL,
    competicion VARCHAR(100),
    rival VARCHAR(100),
    local_visitante ENUM('local', 'visitante'),
    goles_favor INT,
    goles_contra INT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_temporada) REFERENCES temporadas(id_temporada)
);

CREATE TABLE estadisticas_jugador_partido (
    id_estadistica INT AUTO_INCREMENT PRIMARY KEY,
    id_partido INT,
    id_jugador INT,
    posicion_jugada VARCHAR(10),
    minutos_jugados INT,
    goles INT DEFAULT 0,
    asistencias INT DEFAULT 0,
    tiros_totales INT DEFAULT 0,
    tiros_a_puerta INT DEFAULT 0,
    xg FLOAT DEFAULT 0,
    xa FLOAT DEFAULT 0,
    asistencias_tiro INT DEFAULT 0,
    asistencias_segunda INT DEFAULT 0,
    pases_totales INT DEFAULT 0,
    pases_completados INT DEFAULT 0,
    pases_largos_totales INT DEFAULT 0,
    pases_largos_completados INT DEFAULT 0,
    pases_al_area_totales INT DEFAULT 0,
    pases_al_area_completados INT DEFAULT 0,
    pases_profundidad_totales INT DEFAULT 0,
    pases_profundidad_completados INT DEFAULT 0,
    pases_hacia_delante_totales INT DEFAULT 0,
    pases_hacia_delante_completados INT DEFAULT 0,
    pases_hacia_atras_totales INT DEFAULT 0,
    pases_hacia_atras_completados INT DEFAULT 0,
    pases_recibidos INT DEFAULT 0,
    regates_totales INT DEFAULT 0,
    regates_exitosos INT DEFAULT 0,
    carreras_profundidad INT DEFAULT 0,
    duelos_totales INT DEFAULT 0,
    duelos_ganados INT DEFAULT 0,
    duelos_defensivos_totales INT DEFAULT 0,
    duelos_defensivos_ganados INT DEFAULT 0,
    duelos_ofensivos_totales INT DEFAULT 0,
    duelos_ofensivos_ganados INT DEFAULT 0,
    duelos_aereos_totales INT DEFAULT 0,
    duelos_aereos_ganados INT DEFAULT 0,
    intercepciones INT DEFAULT 0,
    despejes INT DEFAULT 0,
    balones_recuperados_campo_rival INT DEFAULT 0,
    balones_perdidos_campo_propio INT DEFAULT 0,
    tarjeta_amarilla TINYINT(1) DEFAULT 0,
    tarjeta_roja TINYINT(1) DEFAULT 0,
    archivo_origen VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_partido) REFERENCES partidos(id_partido),
    FOREIGN KEY (id_jugador) REFERENCES jugadores(id_jugador)
);

CREATE TABLE configuraciones_pesos (
    id_configuracion INT AUTO_INCREMENT PRIMARY KEY,
    nombre_configuracion VARCHAR(100),
    activa TINYINT(1) DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE puntuaciones (
    id_puntuacion INT AUTO_INCREMENT PRIMARY KEY,
    id_partido INT,
    id_jugador INT,
    posicion_evaluada VARCHAR(10),
    puntuacion_ataque FLOAT,
    puntuacion_construccion FLOAT,
    puntuacion_defensa FLOAT,
    factor_minutos FLOAT,
    puntuacion_final FLOAT,
    explicacion_positiva TEXT,
    explicacion_negativa TEXT,
    version_modelo VARCHAR(20),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_partido) REFERENCES partidos(id_partido),
    FOREIGN KEY (id_jugador) REFERENCES jugadores(id_jugador)
);

CREATE TABLE lesiones (
    id_lesion INT AUTO_INCREMENT PRIMARY KEY,
    id_jugador INT,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE,
    tipo_lesion VARCHAR(100),
    gravedad VARCHAR(50),
    fecha_prevista_retorno DATE,
    observaciones TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_jugador) REFERENCES jugadores(id_jugador)
);

CREATE TABLE pesos_bloque_posicion (
    id_peso INT AUTO_INCREMENT PRIMARY KEY,
    id_configuracion INT,
    codigo_posicion VARCHAR(10),
    porcentaje_ataque FLOAT,
    porcentaje_construccion FLOAT,
    porcentaje_defensa FLOAT,
    FOREIGN KEY (id_configuracion) REFERENCES configuraciones_pesos(id_configuracion),
    FOREIGN KEY (codigo_posicion) REFERENCES posiciones(codigo_posicion)
);

CREATE TABLE pesos_metrica_posicion (
    id_peso_metrica INT AUTO_INCREMENT PRIMARY KEY,
    id_configuracion INT,
    codigo_posicion VARCHAR(10),
    bloque ENUM('ataque', 'construccion', 'defensa'),
    clave_metrica VARCHAR(50),
    porcentaje FLOAT,
    penaliza TINYINT(1) DEFAULT 0,
    FOREIGN KEY (id_configuracion) REFERENCES configuraciones_pesos(id_configuracion),
    FOREIGN KEY (codigo_posicion) REFERENCES posiciones(codigo_posicion)
);

CREATE TABLE notas_entrenador (
    id_nota INT AUTO_INCREMENT PRIMARY KEY,
    id_partido INT,
    id_jugador INT,
    nota FLOAT,
    comentario TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_partido) REFERENCES partidos(id_partido),
    FOREIGN KEY (id_jugador) REFERENCES jugadores(id_jugador)
);