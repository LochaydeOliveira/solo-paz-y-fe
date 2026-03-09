-- Remover usuário antigo se existir
DELETE FROM usuarios WHERE email = 'admin@seusite.com';

-- Inserir novo usuário administrador
INSERT INTO usuarios (
    nome,
    email,
    senha,
    tipo,
    status,
    ultimo_login,
    biografia,
    criado_em
) VALUES (
    'Administrador',
    'lochaydeguerreiro@hotmail.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'ativo',
    NOW(),
    'Administrador do site Brasil Hilário',
    NOW()
); 