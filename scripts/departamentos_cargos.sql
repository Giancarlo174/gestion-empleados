-- Departamentos
TRUNCATE TABLE departamento;

INSERT INTO departamento (codigo, nombre) VALUES
('01', 'Dirección General'),
('02', 'Recursos Humanos'),
('03', 'Finanzas y Contabilidad'),
('04', 'Tecnología de la Información'),
('05', 'Marketing y Ventas'),
('06', 'Producción'),
('07', 'Logística y Distribución'),
('08', 'Servicio al Cliente'),
('09', 'Investigación y Desarrollo'),
('10', 'Calidad'),
('11', 'Legal'),
('12', 'Compras y Adquisiciones');

-- Cargos
TRUNCATE TABLE cargo;

-- Dirección General
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('01', '01', 'Director General / CEO'),
('01', '02', 'Director Ejecutivo / COO'),
('01', '03', 'Asistente de Dirección'),
('01', '04', 'Secretario/a Ejecutivo/a'),
('01', '05', 'Coordinador de Proyectos');

-- Recursos Humanos
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('02', '01', 'Director de Recursos Humanos'),
('02', '02', 'Jefe de Selección y Contratación'),
('02', '03', 'Jefe de Capacitación y Desarrollo'),
('02', '04', 'Analista de Recursos Humanos'),
('02', '05', 'Especialista en Nómina'),
('02', '06', 'Asistente de Recursos Humanos');

-- Finanzas y Contabilidad
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('03', '01', 'Director Financiero / CFO'),
('03', '02', 'Contador General'),
('03', '03', 'Jefe de Tesorería'),
('03', '04', 'Analista Financiero'),
('03', '05', 'Contador'),
('03', '06', 'Asistente Contable');

-- Tecnología de la Información
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('04', '01', 'Director de Tecnología / CTO'),
('04', '02', 'Jefe de Infraestructura'),
('04', '03', 'Jefe de Desarrollo'),
('04', '04', 'Desarrollador Senior'),
('04', '05', 'Desarrollador Junior'),
('04', '06', 'Analista de Sistemas'),
('04', '07', 'Administrador de Base de Datos'),
('04', '08', 'Soporte Técnico');

-- Marketing y Ventas
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('05', '01', 'Director de Marketing'),
('05', '02', 'Gerente de Ventas'),
('05', '03', 'Ejecutivo de Ventas'),
('05', '04', 'Coordinador de Marketing Digital'),
('05', '05', 'Diseñador Gráfico'),
('05', '06', 'Especialista en Redes Sociales'),
('05', '07', 'Analista de Mercado');

-- Producción
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('06', '01', 'Director de Producción'),
('06', '02', 'Jefe de Planta'),
('06', '03', 'Supervisor de Línea'),
('06', '04', 'Operario de Producción'),
('06', '05', 'Técnico de Mantenimiento'),
('06', '06', 'Ingeniero de Procesos');

-- Logística y Distribución
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('07', '01', 'Director de Logística'),
('07', '02', 'Jefe de Almacén'),
('07', '03', 'Coordinador de Transporte'),
('07', '04', 'Analista de Inventario'),
('07', '05', 'Operario de Almacén'),
('07', '06', 'Conductor');

-- Servicio al Cliente
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('08', '01', 'Director de Servicio al Cliente'),
('08', '02', 'Supervisor de Call Center'),
('08', '03', 'Representante de Servicio al Cliente'),
('08', '04', 'Gestor de Reclamos'),
('08', '05', 'Especialista en Soporte Técnico');

-- Investigación y Desarrollo
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('09', '01', 'Director de I+D'),
('09', '02', 'Investigador Senior'),
('09', '03', 'Investigador Junior'),
('09', '04', 'Ingeniero de Producto'),
('09', '05', 'Técnico de Laboratorio');

-- Calidad
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('10', '01', 'Director de Calidad'),
('10', '02', 'Jefe de Control de Calidad'),
('10', '03', 'Inspector de Calidad'),
('10', '04', 'Analista de Mejora Continua'),
('10', '05', 'Auditor de Calidad');

-- Legal
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('11', '01', 'Director Legal / Asesor Jurídico'),
('11', '02', 'Abogado Corporativo'),
('11', '03', 'Asistente Legal'),
('11', '04', 'Especialista en Propiedad Intelectual');

-- Compras y Adquisiciones
INSERT INTO cargo (dep_codigo, codigo, nombre) VALUES
('12', '01', 'Director de Compras'),
('12', '02', 'Jefe de Adquisiciones'),
('12', '03', 'Comprador'),
('12', '04', 'Analista de Compras'),
('12', '05', 'Asistente de Compras');
