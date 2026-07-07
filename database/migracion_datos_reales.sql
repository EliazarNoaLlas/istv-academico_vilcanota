-- ============================================================
-- MIGRACION DE DATOS REALES
-- Origen: dump vilcanotaistv (esquema antiguo, con datos reales)
-- Destino: vilcanotaistv_laravel_test (esquema nuevo Laravel)
--
-- IMPORTANTE ANTES DE EJECUTAR:
-- 1) Haz un backup de tu base de datos actual.
-- 2) Este script asume que las tablas cursos, horarios y
--    sesiones_aprendizaje estan vacias o no contienen aun estos
--    registros (no tienen clave unica de negocio, asi que si lo
--    ejecutas dos veces, esas tablas se duplicaran).
-- 3) Las demas tablas usan INSERT IGNORE apoyandose en sus claves
--    unicas (codigo, correo, dni, etc.), por lo que son seguras de
--    re-ejecutar sin duplicar datos.
-- 4) Los password_hash migrados son los hashes de DEMO del sistema
--    anterior (sha256-demo). Cambia las contrasenas reales antes de
--    usar este sistema en produccion.
-- 5) Tu configuracion_sistema indica 'horarios_protegidos = 1'
--    (la tabla horarios no deberia modificarse desde scripts). Si
--    esa proteccion tambien aplica a esta carga inicial, avisame y
--    quito la seccion de horarios.
-- ============================================================

SET NAMES utf8mb4;
START TRANSACTION;

-- ------------------------------------------------------------
-- 1. ROLES
-- ------------------------------------------------------------
INSERT IGNORE INTO roles (codigo, nombre, descripcion, estado, fecha_creacion) VALUES
('director','Director Academico','Acceso global a gestion academica','ACTIVO','2026-06-22 01:46:11'),
('jua','Jefe de Unidad Academica','Revision y aprobacion academica','ACTIVO','2026-06-22 01:46:11'),
('coordinador','Coordinador Academico','Gestion de cursos, docentes y seguimiento','ACTIVO','2026-06-22 01:46:11'),
('docente','Docente','Registro de notas, asistencia y portafolio','ACTIVO','2026-06-22 01:46:11');

-- ------------------------------------------------------------
-- 2. PROGRAMAS DE ESTUDIO
-- ------------------------------------------------------------
INSERT IGNORE INTO programas_estudio (codigo, nombre, familia_profesional, duracion_ciclos, estado) VALUES
('DSI','Desarrollo de Sistemas de Informacion','Computacion e Informatica',6,'ACTIVO'),
('AGRO','Produccion Agropecuaria','Actividades Agrarias',6,'ACTIVO'),
('ENF','Enfermeria Tecnica','Salud',6,'ACTIVO'),
('CON','Construccion Civil','Construccion',6,'ACTIVO'),
('CTB','Contabilidad','Administracion y Comercio',6,'ACTIVO');

-- ------------------------------------------------------------
-- 3. PERIODOS ACADEMICOS
-- ------------------------------------------------------------
INSERT IGNORE INTO periodos_academicos (codigo, nombre, fecha_inicio, fecha_fin, estado) VALUES
('2026-I','Semestre Academico 2026-I','2026-03-16','2026-07-31','ACTIVO'),
('2026-II','Semestre Academico 2026-II','2026-08-17','2026-12-18','PLANIFICADO'),
('2026-V','Semestre 2026-V',NULL,NULL,'PLANIFICADO'),
('2026-III','Semestre 2026-III',NULL,NULL,'PLANIFICADO');

-- ------------------------------------------------------------
-- 4. CONFIGURACION DEL SISTEMA
-- ------------------------------------------------------------
INSERT IGNORE INTO configuracion_sistema (clave, valor, descripcion) VALUES
('nota_minima_aprobatoria','10.5','Nota minima para aprobar una unidad didactica'),
('porcentaje_riesgo_asistencia','70','Umbral de asistencia para alerta academica'),
('ia_predictiva_modelo','reglas-academicas-v1','Modelo activo para deteccion preventiva'),
('horarios_protegidos','1','La tabla horarios no debe modificarse desde scripts complementarios');

-- ------------------------------------------------------------
-- 5. AULAS
-- ------------------------------------------------------------
INSERT IGNORE INTO aulas (codigo, nombre, tipo, capacidad, ubicacion, estado) VALUES
('A201','Aula 201','AULA',35,'Pabellon A','DISPONIBLE'),
('A202','Aula 202','AULA',35,'Pabellon A','DISPONIBLE'),
('A203','Aula 203','AULA',30,'Pabellon A','DISPONIBLE'),
('LAB-COMP','Laboratorio de Computo','LABORATORIO',28,'Pabellon B','DISPONIBLE'),
('LAB-REDES','Laboratorio de Redes','LABORATORIO',24,'Pabellon B','DISPONIBLE');

-- ------------------------------------------------------------
-- 6. ESTRUCTURA DE SILABO Y CRITERIOS
-- ------------------------------------------------------------
INSERT IGNORE INTO silabo_estructuras (codigo, nombre, version, descripcion, activo, fecha_creacion) VALUES
('SILABO_ISTV_2026','Estructura institucional de silabo ISTV Vilcanota','2026.1','Rubrica oficial para validar silabos subidos al portafolio docente.',1,'2026-07-02 20:00:58');

INSERT IGNORE INTO silabo_estructura_criterios (id_estructura, orden, seccion, descripcion, campos_json, validaciones_json, peso, obligatorio, fecha_creacion) VALUES
((SELECT id_estructura FROM silabo_estructuras WHERE codigo='SILABO_ISTV_2026'),1,'Datos Generales','Debe identificar el curso, docente, periodo y datos academicos basicos.','["Programa de estudios","Modulo formativo","Unidad didactica","Creditos","Horas totales","Horas semanales","Periodo lectivo","Periodo academico","Fecha de inicio y termino","Turno","Docente","Correo institucional","Aula virtual"]','["No debe estar vacio","Los datos deben corresponder al curso esperado","El docente y curso deben poder identificarse"]',15.00,1,'2026-07-02 20:00:58'),
((SELECT id_estructura FROM silabo_estructuras WHERE codigo='SILABO_ISTV_2026'),2,'Sumilla','Debe contener una descripcion del curso vinculada a la unidad didactica.','["Descripcion del curso","Relacion con la unidad didactica"]','["Debe corresponder al nombre de la unidad didactica","No debe ser texto generico de otro curso"]',10.00,1,'2026-07-02 20:00:58'),
((SELECT id_estructura FROM silabo_estructuras WHERE codigo='SILABO_ISTV_2026'),3,'Unidad de Competencia','Debe declarar la competencia especifica del modulo.','["Competencia especifica del modulo"]','["La competencia debe existir y guardar relacion con el curso"]',10.00,1,'2026-07-02 20:00:58'),
((SELECT id_estructura FROM silabo_estructuras WHERE codigo='SILABO_ISTV_2026'),4,'Capacidades e Indicadores','Debe listar capacidades e indicadores de logro.','["Capacidades","Indicadores de logro"]','["Debe existir al menos una capacidad","Debe existir al menos un indicador","Los indicadores deben relacionarse con las capacidades"]',12.00,1,'2026-07-02 20:00:58'),
((SELECT id_estructura FROM silabo_estructuras WHERE codigo='SILABO_ISTV_2026'),5,'Competencias para la empleabilidad','Debe incluir competencias transversales.','["Comunicacion","Etica","Emprendimiento"]','["Debe mencionar competencias transversales pertinentes"]',8.00,1,'2026-07-02 20:00:58'),
((SELECT id_estructura FROM silabo_estructuras WHERE codigo='SILABO_ISTV_2026'),6,'Programacion de sesiones','Cada sesion debe tener datos minimos de programacion y evaluacion.','["Semana","Numero de sesion","Contenido","Logro de aprendizaje","Instrumento de evaluacion"]','["Las sesiones deben relacionarse con las capacidades","No debe haber sesiones vacias","Debe existir instrumento de evaluacion por sesion o bloque evaluativo"]',18.00,1,'2026-07-02 20:00:58'),
((SELECT id_estructura FROM silabo_estructuras WHERE codigo='SILABO_ISTV_2026'),7,'Metodologia','Debe describir la metodologia de ensenanza.','["Metodologia de ensenanza"]','["Debe describir como se desarrollara el curso"]',7.00,1,'2026-07-02 20:00:58'),
((SELECT id_estructura FROM silabo_estructuras WHERE codigo='SILABO_ISTV_2026'),8,'Ambientes y Recursos','Debe indicar ambientes y recursos utilizados.','["Ambientes","Recursos","Medios y materiales"]','["Debe contener recursos concretos y relacionados al curso"]',6.00,1,'2026-07-02 20:00:58'),
((SELECT id_estructura FROM silabo_estructuras WHERE codigo='SILABO_ISTV_2026'),9,'Sistema de Evaluacion','Debe explicar como se evaluara el curso.','["Sistema de evaluacion","Criterios","Instrumentos","Condiciones de aprobacion"]','["Debe describir el sistema de evaluacion","Debe ser coherente con las sesiones e indicadores"]',8.00,1,'2026-07-02 20:00:58'),
((SELECT id_estructura FROM silabo_estructuras WHERE codigo='SILABO_ISTV_2026'),10,'Bibliografia','Debe incluir bibliografia y referencias web relacionadas al curso.','["Bibliografia","Referencias web"]','["No debe estar vacia","Debe relacionarse con el curso indicado"]',6.00,1,'2026-07-02 20:00:58');

-- ------------------------------------------------------------
-- 7. USUARIOS (staff + docentes)
--    NOTA: password_hash son hashes DEMO. Cambiar antes de produccion.
-- ------------------------------------------------------------
INSERT IGNORE INTO usuarios (id_rol, usuario, correo, password_hash, password_algoritmo, nombres, apellidos, dni, estado, fecha_creacion) VALUES
((SELECT id_rol FROM roles WHERE codigo='director'),'director','director@istv.edu.pe','5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5','sha256-demo','Director','Academico','00000000','ACTIVO','2026-06-22 01:46:11'),
((SELECT id_rol FROM roles WHERE codigo='jua'),'jua','jua@istv.edu.pe','5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5','sha256-demo','JUA','Academico','00000001','ACTIVO','2026-06-22 01:46:11'),
((SELECT id_rol FROM roles WHERE codigo='coordinador'),'coordinador','coordinador@istv.edu.pe','5994471abb01112afcc18159f6cc74b4f511b99806da59b3caf5a9c173cacfc5','sha256-demo','Coordinador','Academico','00000002','ACTIVO','2026-06-22 01:46:11'),
((SELECT id_rol FROM roles WHERE codigo='docente'),'d.huaylla','d.huaylla@istv.edu.pe','85d49a0c207f8bba5fc713ba6a01d091e732bce604891ce8548f7a77e8d92a43','sha256-demo','Diana','Huaylla','00000001','ACTIVO','2026-06-22 01:46:11'),
((SELECT id_rol FROM roles WHERE codigo='docente'),'j.barrientos','j.barrientos@istv.edu.pe','5c55b95deca65d2dbf3a9d9a0d45a106e918650ae8fe74c2a61d774434ac1c9c','sha256-demo','Jhon','Barrientos Ferro','00000002','ACTIVO','2026-06-22 01:46:11'),
((SELECT id_rol FROM roles WHERE codigo='docente'),'h.palomino','h.palomino@istv.edu.pe','8f3e96dfb477b08d253c289857518b34290e9b72d7b56ffefa6ddb533afe5749','sha256-demo','Hernan','Palomino','00000003','ACTIVO','2026-06-22 01:46:11'),
((SELECT id_rol FROM roles WHERE codigo='docente'),'r.jara','r.jara@istv.edu.pe','7f12147648a7c4566b8b48e86058dcc74aeee4bc3c7b3cc9b8934a08b86345bb','sha256-demo','Rosa Luz','Jara','00000004','ACTIVO','2026-06-22 01:46:11'),
((SELECT id_rol FROM roles WHERE codigo='docente'),'f.quispe','f.quispe@istv.edu.pe','e95a5197ffb80c4edd24bf5c26a64b7f8694742ef6b22b1d99e1fd6717a9125f','sha256-demo','Fredy','Quispe','00000005','ACTIVO','2026-06-22 01:46:11'),
((SELECT id_rol FROM roles WHERE codigo='docente'),'p.lech','p.lech@istv.edu.pe','f22427ebfec7380121317f4be1a9076718bc405532e526dc53de2ea803e9c0d5','sha256-demo','Pavel','Lech','00000006','ACTIVO','2026-06-22 01:46:11'),
((SELECT id_rol FROM roles WHERE codigo='docente'),'f.cornejo','f.cornejo@istv.edu.pe','416a134e76e08e3bc14fdcd0755796a71371c1426f33868621cb083257790e07','sha256-demo','Fernando','Cornejo','00000007','ACTIVO','2026-06-22 01:46:11');

-- ------------------------------------------------------------
-- 8. DOCENTES
-- ------------------------------------------------------------
INSERT IGNORE INTO docentes (id_usuario, codigo_docente, especialidad, tipo_docente, estado_academico, fecha_registro) VALUES
((SELECT id_usuario FROM usuarios WHERE usuario='d.huaylla'),'DOC001','Desarrollo de Sistemas','ESPECIFICO','ACTIVO','2026-06-10 19:02:48'),
((SELECT id_usuario FROM usuarios WHERE usuario='j.barrientos'),'DOC002','Desarrollo de Sistemas','ESPECIFICO','ACTIVO','2026-06-10 19:02:48'),
((SELECT id_usuario FROM usuarios WHERE usuario='h.palomino'),'DOC003','Desarrollo de Sistemas','ESPECIFICO','ACTIVO','2026-06-10 19:02:48'),
((SELECT id_usuario FROM usuarios WHERE usuario='r.jara'),'DOC004','Desarrollo de Sistemas','ESPECIFICO','ACTIVO','2026-06-10 19:02:48'),
((SELECT id_usuario FROM usuarios WHERE usuario='f.quispe'),'DOC005','Desarrollo de Sistemas','ESPECIFICO','ACTIVO','2026-06-10 19:02:48'),
((SELECT id_usuario FROM usuarios WHERE usuario='p.lech'),'DOC006','Desarrollo de Sistemas','ESPECIFICO','ACTIVO','2026-06-10 19:02:48'),
((SELECT id_usuario FROM usuarios WHERE usuario='f.cornejo'),'DOC007','Desarrollo de Sistemas','ESPECIFICO','ACTIVO','2026-06-10 19:02:48');

-- ------------------------------------------------------------
-- 9. ESTUDIANTES
-- ------------------------------------------------------------
INSERT IGNORE INTO estudiantes (codigo_estudiante, dni, nombres, apellido_paterno, apellido_materno, correo, telefono, id_programa, ciclo, estado, fecha_registro) VALUES
('2024-0158','74000158','Maria','Condori','Apaza','maria.condori@istv.edu.pe','900000158',(SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),'III','REGULAR','2026-06-22 01:19:15'),
('2024-0203','74000203','Juan','Quispe','Huanca','juan.quispe@istv.edu.pe','900000203',(SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),'II','OBSERVADO','2026-06-22 01:19:15'),
('2023-0091','73000091','Pedro','Ccahuana','Lima','pedro.ccahuana@istv.edu.pe','900000091',(SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),'V','RIESGO','2026-06-22 01:19:15'),
('2023-0142','73000142','Ana','Ticona','Roque','ana.ticona@istv.edu.pe','900000142',(SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),'V','OBSERVADO','2026-06-22 01:19:15'),
('2026-0031','76000031','Rosa','Mamani','Ccoa','rosa.mamani@istv.edu.pe','900000031',(SELECT id_programa FROM programas_estudio WHERE codigo='AGRO'),'I','REGULAR','2026-06-22 01:19:15'),
('2026-0032','76000032','Carlos','Turpo','Flores','carlos.turpo@istv.edu.pe','900000032',(SELECT id_programa FROM programas_estudio WHERE codigo='AGRO'),'III','RIESGO','2026-06-22 01:19:15'),
('2023-0145','73000145','Elena','Ticona','Roque','elena.ticona@istv.edu.pe','900000145',(SELECT id_programa FROM programas_estudio WHERE codigo='ENF'),'V','REGULAR','2026-06-22 01:19:15');

-- ------------------------------------------------------------
-- 10. CURSOS  (sin clave unica: ejecutar una sola vez)
--     Todos pertenecen al programa DSI segun el catalogo del dump.
-- ------------------------------------------------------------
INSERT INTO cursos (id_programa, id_docente, nombre_curso, modulo, semestre, creditos, horas_teoria, horas_practica, horas_ud, total_teoria, total_practica, total_horas, estado) VALUES
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Lógica de programación','Módulo I','I',5,1,2,3,16,64,80,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC007'),'Diseño de software','Módulo I','I',6,2,2,4,32,64,96,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Modelamiento de bases de datos','Módulo I','I',7,1,3,4,16,96,112,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Técnicas de programación','Módulo I','I',6,2,2,4,32,64,96,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Comunicación oral','Módulo I','I',3,1,1,2,16,32,48,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Aplicaciones en internet','Módulo I','I',3,1,1,2,16,32,48,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Algoritmos y estructuras de datos','Módulo I','II',6,2,2,6,32,64,96,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Diseño web','Módulo I','II',6,2,2,6,32,64,96,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Gestión de base de datos','Módulo I','II',7,1,3,7,16,96,112,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Programación orientada a objetos','Módulo I','II',5,1,2,5,16,64,80,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Interpretación y producción textos','Módulo I','II',3,1,1,3,16,32,48,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC007'),'Ofimática','Módulo I','II',3,1,1,3,16,32,48,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Administración de sitios web','Módulo II','III',8,2,3,5,32,96,128,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Seguridad informática','Módulo II','III',3,1,1,2,16,32,48,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Aplicaciones web','Módulo II','III',9,1,4,5,16,128,144,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Aplicaciones móviles','Módulo II','III',7,1,3,4,16,96,112,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Inglés para la comunicación oral','Módulo II','III',3,1,1,2,16,32,48,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Lenguaje de programación concurrente','Módulo II','IV',8,2,3,8,32,96,128,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Lenguaje de programación web dinámico','Módulo II','IV',8,2,3,8,32,96,128,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Base de datos no relacionales','Módulo II','V',7,1,3,7,16,96,112,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Modelamiento de software de entretenimiento','Módulo II','IV',5,1,2,5,16,64,80,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Comprensión y redacción en inglés','Módulo II','IV',2,0,1,2,0,32,32,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Gestión de proyectos de TI','Módulo III','V',5,1,2,5,16,64,80,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Pruebas y calidad del software','Módulo III','V',7,1,3,7,16,96,112,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Inteligencia de negocios','Módulo III','V',5,1,2,5,16,64,80,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Gestión de servicios de TI','Módulo III','V',7,1,3,7,16,96,112,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC007'),'Fundamentos de innovación tecnológica','Módulo III','V',3,1,1,3,16,32,48,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Comportamiento ético','Módulo III','V',3,1,1,3,16,32,48,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),NULL,'Gestión de servidores','Módulo III','VI',5,1,2,3,16,64,80,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),NULL,'Gestión de redes informáticas','Módulo III','VI',7,1,3,4,16,96,112,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),NULL,'Soporte de auditoría de TI','Módulo III','VI',6,2,2,4,32,64,96,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),NULL,'Auditoría de software','Módulo III','VI',3,1,1,2,16,32,48,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),NULL,'Inteligencia artificial','Módulo III','VI',3,1,1,2,16,32,48,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),NULL,'Solución de problemas','Módulo III','VI',3,1,1,2,16,32,48,'ACTIVO'),
((SELECT id_programa FROM programas_estudio WHERE codigo='DSI'),NULL,'Innovación tecnológica','Módulo III','VI',3,1,1,2,16,32,48,'ACTIVO');

-- ------------------------------------------------------------
-- 11. HORARIOS  (sin clave unica: ejecutar una sola vez)
--     Recuerda: configuracion_sistema.horarios_protegidos = 1
-- ------------------------------------------------------------
INSERT INTO horarios (id_curso, id_docente, dia, hora_inicio, hora_fin, aula, estado, fuente) VALUES
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Lunes','08:00:00','08:45:00','A202','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Lunes','08:45:00','09:30:00','A203','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Lunes','09:30:00','10:15:00','A202','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Martes','08:00:00','08:45:00','A202','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Martes','08:45:00','09:30:00','A203','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Martes','09:30:00','10:15:00','A201','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Miércoles','08:00:00','08:45:00','A201','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Administración de sitios web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Jueves','09:30:00','10:15:00','Invernadero','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Administración de sitios web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Miércoles','12:00:00','12:45:00','Campo Experimental','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Administración de sitios web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Jueves','10:15:00','11:00:00','A205','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Administración de sitios web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Jueves','08:45:00','09:30:00','A205','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Administración de sitios web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Miércoles','11:15:00','12:00:00','A205','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Administración de sitios web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Martes','12:00:00','12:45:00','A203','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Administración de sitios web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Lunes','11:15:00','12:00:00','Campo Experimental','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Administración de sitios web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Viernes','10:15:00','11:00:00','A201','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Seguridad informática'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Martes','11:15:00','12:00:00','Lab. Cómputo','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Seguridad informática'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Jueves','11:15:00','12:00:00','A201','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Seguridad informática'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Jueves','12:00:00','12:45:00','A202','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Miércoles','08:45:00','09:30:00','A204','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),'Miércoles','09:30:00','10:15:00','A203','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones móviles'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Martes','10:15:00','11:00:00','Lab. Cómputo','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones móviles'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Miércoles','10:15:00','11:00:00','A204','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones móviles'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Lunes','12:00:00','12:45:00','Lab. Cómputo','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones móviles'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Viernes','08:00:00','08:45:00','A203','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones móviles'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Viernes','08:45:00','09:30:00','A204','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones móviles'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Viernes','09:30:00','10:15:00','A205','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones móviles'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Jueves','08:00:00','08:45:00','A205','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Inglés para la comunicación oral'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Viernes','12:00:00','12:45:00','A203','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Inglés para la comunicación oral'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Lunes','10:15:00','11:00:00','A204','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Inglés para la comunicación oral'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Viernes','11:15:00','12:00:00','A205','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Lunes','08:00:00','08:45:00','A201','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Lunes','08:45:00','09:30:00','A201','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Lunes','09:30:00','10:15:00','A201','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Martes','08:00:00','08:45:00','A203','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Jueves','08:45:00','09:30:00','Lab. Cómputo','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Diseño de software'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC007'),'Miércoles','08:00:00','08:45:00','A202','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Diseño de software'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC007'),'Miércoles','08:45:00','09:30:00','A202','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Diseño de software'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC007'),'Martes','09:30:00','10:15:00','Lab. Cómputo','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Diseño de software'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC007'),'Lunes','12:00:00','12:45:00','A203','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Diseño de software'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC007'),'Martes','10:15:00','11:00:00','A201','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Diseño de software'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC007'),'Miércoles','09:30:00','10:15:00','Invernadero','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Modelamiento de bases de datos'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Martes','08:45:00','09:30:00','Campo Experimental','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Modelamiento de bases de datos'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Jueves','08:00:00','08:45:00','A203','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Modelamiento de bases de datos'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Viernes','11:15:00','12:00:00','Campo Experimental','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Modelamiento de bases de datos'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Miércoles','11:15:00','12:00:00','Invernadero','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Modelamiento de bases de datos'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Miércoles','12:00:00','12:45:00','Invernadero','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Modelamiento de bases de datos'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Lunes','11:15:00','12:00:00','A202','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Modelamiento de bases de datos'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),'Viernes','12:00:00','12:45:00','Lab. Redes','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Técnicas de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Martes','11:15:00','12:00:00','A204','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Técnicas de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Miércoles','10:15:00','11:00:00','A201','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Técnicas de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Martes','12:00:00','12:45:00','Lab. Cómputo','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Técnicas de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Jueves','11:15:00','12:00:00','A203','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Técnicas de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Jueves','12:00:00','12:45:00','Invernadero','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Técnicas de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),'Viernes','10:15:00','11:00:00','A205','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Comunicación oral'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Lunes','10:15:00','11:00:00','Campo Experimental','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Comunicación oral'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Jueves','09:30:00','10:15:00','A202','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Comunicación oral'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Jueves','10:15:00','11:00:00','A201','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones en internet'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Viernes','08:45:00','09:30:00','A201','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones en internet'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Viernes','08:00:00','08:45:00','A202','Confirmado','MANUAL'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones en internet'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),'Viernes','09:30:00','10:15:00','A203','Confirmado','MANUAL');

-- ------------------------------------------------------------
-- 12. MATRICULAS
-- ------------------------------------------------------------
INSERT IGNORE INTO matriculas (id_estudiante, id_periodo, ciclo, estado, fecha_matricula) VALUES
((SELECT id_estudiante FROM estudiantes WHERE codigo_estudiante='2024-0158'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'III','MATRICULADO','2026-03-10'),
((SELECT id_estudiante FROM estudiantes WHERE codigo_estudiante='2024-0203'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'II','MATRICULADO','2026-03-10'),
((SELECT id_estudiante FROM estudiantes WHERE codigo_estudiante='2023-0091'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'V','MATRICULADO','2026-03-10'),
((SELECT id_estudiante FROM estudiantes WHERE codigo_estudiante='2023-0142'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'V','MATRICULADO','2026-03-10'),
((SELECT id_estudiante FROM estudiantes WHERE codigo_estudiante='2026-0031'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'I','MATRICULADO','2026-03-10'),
((SELECT id_estudiante FROM estudiantes WHERE codigo_estudiante='2026-0032'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'III','MATRICULADO','2026-03-10'),
((SELECT id_estudiante FROM estudiantes WHERE codigo_estudiante='2023-0145'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'V','MATRICULADO','2026-03-10');

-- ------------------------------------------------------------
-- 13. MATRICULA_CURSOS
-- ------------------------------------------------------------
INSERT IGNORE INTO matricula_cursos (id_matricula, id_curso, estado) VALUES
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2026-0031'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2026-0031'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Diseño de software'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2026-0031'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Modelamiento de bases de datos'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2026-0031'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Técnicas de programación'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2026-0031'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Comunicación oral'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2026-0031'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones en internet'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2024-0203'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Algoritmos y estructuras de datos'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2024-0203'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Diseño web'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2024-0203'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Gestión de base de datos'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2024-0203'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Programación orientada a objetos'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2024-0203'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Interpretación y producción textos'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2024-0203'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Ofimática'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2024-0158'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Administración de sitios web'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2026-0032'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Administración de sitios web'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2024-0158'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Seguridad informática'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2026-0032'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Seguridad informática'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2024-0158'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2026-0032'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2024-0158'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones móviles'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2026-0032'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones móviles'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2024-0158'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Inglés para la comunicación oral'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2026-0032'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Inglés para la comunicación oral'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0091'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Base de datos no relacionales'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0142'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Base de datos no relacionales'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0145'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Base de datos no relacionales'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0091'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Gestión de proyectos de TI'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0142'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Gestión de proyectos de TI'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0145'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Gestión de proyectos de TI'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0091'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Pruebas y calidad del software'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0142'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Pruebas y calidad del software'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0145'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Pruebas y calidad del software'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0091'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Inteligencia de negocios'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0142'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Inteligencia de negocios'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0145'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Inteligencia de negocios'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0091'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Gestión de servicios de TI'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0142'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Gestión de servicios de TI'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0145'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Gestión de servicios de TI'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0091'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Fundamentos de innovación tecnológica'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0142'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Fundamentos de innovación tecnológica'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0145'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Fundamentos de innovación tecnológica'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0091'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Comportamiento ético'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0142'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Comportamiento ético'),'EN_CURSO'),
((SELECT mt.id_matricula FROM matriculas mt JOIN estudiantes e ON e.id_estudiante=mt.id_estudiante WHERE e.codigo_estudiante='2023-0145'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Comportamiento ético'),'EN_CURSO');

-- ------------------------------------------------------------
-- 14. NOTAS (unidad I) - se enlazan via estudiante+curso
-- ------------------------------------------------------------
-- Nota tipica: practica=13, teoria=14, examen=15 (estudiantes con matricula regular)
INSERT IGNORE INTO notas (id_matricula_curso, unidad, practica, teoria, examen, estado, fecha_registro)
SELECT mc.id_matricula_curso, 'I', v.practica, v.teoria, v.examen, 'ABIERTO', '2026-06-22 01:19:15'
FROM (
  SELECT '2024-0158' codigo_estudiante,'Administración de sitios web' nombre_curso,13.00 practica,14.00 teoria,15.00 examen
  UNION ALL SELECT '2024-0158','Seguridad informática',13.00,14.00,15.00
  UNION ALL SELECT '2024-0158','Aplicaciones web',13.00,14.00,15.00
  UNION ALL SELECT '2024-0158','Aplicaciones móviles',13.00,14.00,15.00
  UNION ALL SELECT '2024-0158','Inglés para la comunicación oral',13.00,14.00,15.00
  UNION ALL SELECT '2024-0203','Algoritmos y estructuras de datos',13.00,14.00,15.00
  UNION ALL SELECT '2024-0203','Diseño web',13.00,14.00,15.00
  UNION ALL SELECT '2024-0203','Gestión de base de datos',13.00,14.00,15.00
  UNION ALL SELECT '2024-0203','Programación orientada a objetos',13.00,14.00,15.00
  UNION ALL SELECT '2024-0203','Interpretación y producción textos',13.00,14.00,15.00
  UNION ALL SELECT '2024-0203','Ofimática',13.00,14.00,15.00
  UNION ALL SELECT '2023-0091','Base de datos no relacionales',8.00,9.00,8.00
  UNION ALL SELECT '2023-0091','Gestión de proyectos de TI',8.00,9.00,8.00
  UNION ALL SELECT '2023-0091','Pruebas y calidad del software',8.00,9.00,8.00
  UNION ALL SELECT '2023-0091','Inteligencia de negocios',8.00,9.00,8.00
  UNION ALL SELECT '2023-0091','Gestión de servicios de TI',8.00,9.00,8.00
  UNION ALL SELECT '2023-0091','Fundamentos de innovación tecnológica',8.00,9.00,8.00
  UNION ALL SELECT '2023-0091','Comportamiento ético',8.00,9.00,8.00
  UNION ALL SELECT '2023-0142','Base de datos no relacionales',13.00,14.00,15.00
  UNION ALL SELECT '2023-0142','Gestión de proyectos de TI',13.00,14.00,15.00
  UNION ALL SELECT '2023-0142','Pruebas y calidad del software',13.00,14.00,15.00
  UNION ALL SELECT '2023-0142','Inteligencia de negocios',13.00,14.00,15.00
  UNION ALL SELECT '2023-0142','Gestión de servicios de TI',13.00,14.00,15.00
  UNION ALL SELECT '2023-0142','Fundamentos de innovación tecnológica',13.00,14.00,15.00
  UNION ALL SELECT '2023-0142','Comportamiento ético',13.00,14.00,15.00
  UNION ALL SELECT '2026-0031','Lógica de programación',13.00,14.00,15.00
  UNION ALL SELECT '2026-0031','Diseño de software',13.00,14.00,15.00
  UNION ALL SELECT '2026-0031','Modelamiento de bases de datos',13.00,14.00,15.00
  UNION ALL SELECT '2026-0031','Técnicas de programación',13.00,14.00,15.00
  UNION ALL SELECT '2026-0031','Comunicación oral',13.00,14.00,15.00
  UNION ALL SELECT '2026-0031','Aplicaciones en internet',13.00,14.00,15.00
  UNION ALL SELECT '2026-0032','Administración de sitios web',8.00,9.00,8.00
  UNION ALL SELECT '2026-0032','Seguridad informática',8.00,9.00,8.00
  UNION ALL SELECT '2026-0032','Aplicaciones web',8.00,9.00,8.00
  UNION ALL SELECT '2026-0032','Aplicaciones móviles',8.00,9.00,8.00
  UNION ALL SELECT '2026-0032','Inglés para la comunicación oral',8.00,9.00,8.00
  UNION ALL SELECT '2023-0145','Base de datos no relacionales',13.00,14.00,15.00
  UNION ALL SELECT '2023-0145','Gestión de proyectos de TI',13.00,14.00,15.00
  UNION ALL SELECT '2023-0145','Pruebas y calidad del software',13.00,14.00,15.00
  UNION ALL SELECT '2023-0145','Inteligencia de negocios',13.00,14.00,15.00
  UNION ALL SELECT '2023-0145','Gestión de servicios de TI',13.00,14.00,15.00
  UNION ALL SELECT '2023-0145','Fundamentos de innovación tecnológica',13.00,14.00,15.00
  UNION ALL SELECT '2023-0145','Comportamiento ético',13.00,14.00,15.00
) v
JOIN estudiantes e ON e.codigo_estudiante = v.codigo_estudiante
JOIN matriculas m2 ON m2.id_estudiante = e.id_estudiante AND m2.id_periodo = (SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I')
JOIN (SELECT nombre_curso, MIN(id_curso) AS id_curso FROM cursos GROUP BY nombre_curso) c ON c.nombre_curso = v.nombre_curso
JOIN matricula_cursos mc ON mc.id_matricula = m2.id_matricula AND mc.id_curso = c.id_curso;

-- ------------------------------------------------------------
-- 15. PORTAFOLIO_DOCENTE
-- ------------------------------------------------------------
INSERT IGNORE INTO portafolio_docente (id_docente, id_curso, id_periodo, silabo, sesiones, registro_asistencia, registro_notas, actas, estado, observacion, fecha_actualizacion) VALUES
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC004'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones web'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','INCOMPLETO','Registro inicial generado sin modificar horarios.',NULL),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Administración de sitios web'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','INCOMPLETO','Registro inicial generado sin modificar horarios.',NULL),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Seguridad informática'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','INCOMPLETO','Registro inicial generado sin modificar horarios.',NULL),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones móviles'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','INCOMPLETO','Registro inicial generado sin modificar horarios.',NULL),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Inglés para la comunicación oral'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','INCOMPLETO','Registro inicial generado sin modificar horarios.',NULL),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'APROBADO','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','COMPLETO','IA Groq (llama-3.3-70b-versatile): confianza 0.9 - silabo validado para el curso de Inteligencia de Negocios (Modulo de Administracion de Sistemas de Informacion).','2026-06-25 02:21:36'),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC007'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Diseño de software'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','INCOMPLETO','Registro inicial generado sin modificar horarios.',NULL),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC005'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Modelamiento de bases de datos'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','INCOMPLETO','Registro inicial generado sin modificar horarios.',NULL),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Técnicas de programación'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'EN_REVISION','OBSERVADO','PENDIENTE','PENDIENTE','PENDIENTE','OBSERVADO','Documento rechazado por observaciones de IA: el plan de sesion no cumple los requisitos minimos (faltan datos de sesion, proposito, secuencia didactica, actividades y evidencia de evaluacion).','2026-07-03 00:53:54'),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Comunicación oral'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','INCOMPLETO','Registro inicial generado sin modificar horarios.',NULL),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC003'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Aplicaciones en internet'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','INCOMPLETO','Registro inicial generado sin modificar horarios.',NULL),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Inteligencia de negocios'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-I'),'PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','INCOMPLETO','',NULL),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Programación orientada a objetos'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-II'),'PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','INCOMPLETO','',NULL),
((SELECT id_docente FROM docentes WHERE codigo_docente='DOC006'),(SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Comunicación oral'),(SELECT id_periodo FROM periodos_academicos WHERE codigo='2026-III'),'OBSERVADO','PENDIENTE','PENDIENTE','PENDIENTE','PENDIENTE','OBSERVADO','Documento rechazado por observaciones de IA: no se pudo leer el archivo en el servidor y no se hallaron evidencias claras de las secciones obligatorias del silabo.','2026-07-02 22:34:50');

-- ------------------------------------------------------------
-- 16. PORTAFOLIO_DOCUMENTOS
-- ------------------------------------------------------------
INSERT IGNORE INTO portafolio_documentos (id_portafolio, tipo, titulo, archivo, estado, observacion, fecha_subida) VALUES
((SELECT MIN(pd.id_portafolio) FROM portafolio_docente pd JOIN docentes d ON d.id_docente=pd.id_docente JOIN cursos c ON c.id_curso=pd.id_curso JOIN periodos_academicos p ON p.id_periodo=pd.id_periodo WHERE d.codigo_docente='DOC006' AND c.nombre_curso='Comunicación oral' AND p.codigo='2026-III'),
 'SILABO','Silabo - Inglés para la comunicación oral - 20260703 002633 - b66c','uploads/portafolios/portafolio_1783031193_f8fd699a.docx','OBSERVADO','Documento rechazado por observaciones de IA: no se pudo leer el archivo en el servidor y no se hallaron evidencias claras de las secciones obligatorias.','2026-07-02 17:26:33'),
((SELECT MIN(pd.id_portafolio) FROM portafolio_docente pd JOIN docentes d ON d.id_docente=pd.id_docente JOIN cursos c ON c.id_curso=pd.id_curso JOIN periodos_academicos p ON p.id_periodo=pd.id_periodo WHERE d.codigo_docente='DOC006' AND c.nombre_curso='Técnicas de programación' AND p.codigo='2026-I'),
 'PLAN_SESION','Sesiones de aprendizaje - Técnicas de programación - 20260703 012306 - ddf9','uploads/portafolios/portafolio_1783034586_54d76a0c.pdf','OBSERVADO','Documento rechazado por observaciones de IA: el plan de sesion no cumple los requisitos minimos para el curso en el semestre 2026-I.','2026-07-02 18:23:06');

-- ------------------------------------------------------------
-- 17. SESIONES_APRENDIZAJE (sin clave unica: ejecutar una sola vez)
-- ------------------------------------------------------------
INSERT INTO sesiones_aprendizaje (id_curso, id_docente, titulo, archivo, numero_sesion, estado, fecha_subida) VALUES
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Sesion 3 - Condicionales','uploads/sesiones/sesion_1783034088_c6e12fb2.pdf',3,'PENDIENTE','2026-07-02 18:14:48'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Sesion 1 - Introduccion','uploads/sesiones/sesion_1783034088_06352600.pdf',1,'PENDIENTE','2026-07-02 18:14:48'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Sesion 2 - Variables','uploads/sesiones/sesion_1783034088_8ee494ae.pdf',2,'PENDIENTE','2026-07-02 18:14:48'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Sesion sin numero','uploads/sesiones/sesion_1783034088_3c0af572.pdf',NULL,'PENDIENTE','2026-07-02 18:14:48'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Lógica de programación'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC001'),'Sesion extra sin num','uploads/sesiones/sesion_1783034088_f534cd3e.pdf',NULL,'PENDIENTE','2026-07-02 18:14:48'),
((SELECT MIN(id_curso) FROM cursos WHERE nombre_curso='Comunicación oral'),(SELECT id_docente FROM docentes WHERE codigo_docente='DOC002'),'Sesion 1 - Comunicacion oral','uploads/sesiones/sesion_1783034088_fa784d01.pdf',1,'PENDIENTE','2026-07-02 18:14:48');

COMMIT;

-- ============================================================
-- FIN DEL SCRIPT
-- Tablas NO incluidas (sin datos reales en el dump de origen):
-- alertas_academicas, asistencia_detalle, asistencia_sesiones,
-- auditoria_sistema, horarios_ia_generados, ia_predicciones,
-- mensajes, notificaciones, reportes_generados.
-- Si tienes datos reales para esas tablas, compartelos y agrego
-- los INSERT correspondientes.
-- ============================================================
