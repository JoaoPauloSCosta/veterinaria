-- seed.sql
-- Dados iniciais para o sistema veterinário

SET NAMES utf8mb4;
SET time_zone = '-03:00';
SET foreign_key_checks = 0;

-- Usuários
INSERT INTO users (name, email, password_hash, role, is_active) VALUES
('Administrador', 'admin@clinic.local', '$2y$10$Jq8m7wq9pI9wzv3pP7o2mO0HkI0.EhJz0d8lq6f6GQdXo1cO1v2qK', 'admin', 1), -- senha: Admin@123
('Dra. Ana Vet', 'ana.vet@clinic.local', '$2y$10$h1f5gqJ6jD4bN8u7a0cYQeP8iC3cZ9iZ3Vn2nq7nD3lYpJZJH/5N.', 'veterinario', 1),
('Dr. Bruno Vet', 'bruno.vet@clinic.local', '$2y$10$h1f5gqJ6jD4bN8u7a0cYQeP8iC3cZ9iZ3Vn2nq7nD3lYpJZJH/5N.', 'veterinario', 1),
('Recepção 1', 'recepcao@clinic.local', '$2y$10$2M3a5xY8Z0pQwErTyUiOpO5Ct2t8G6CkZ7l3aB9cD1eF2gH3iJ4Ka', 'recepcao', 1),
('Financeiro 1', 'financeiro@clinic.local', '$2y$10$2M3a5xY8Z0pQwErTyUiOpO5Ct2t8G6CkZ7l3aB9cD1eF2gH3iJ4Ka', 'financeiro', 1);

-- Clientes (5)
INSERT INTO clients (name, cpf_cnpj, email, phone, address) VALUES
('Carlos Silva', '12345678901', 'carlos@example.com', '(11) 99999-1111', 'Rua A, 100'),
('Marina Souza', '98765432100', 'marina@example.com', '(11) 98888-2222', 'Rua B, 200'),
('Paulo Lima', NULL, 'paulo@example.com', '(11) 97777-3333', 'Rua C, 300'),
('Fernanda Costa', '11222333000199', 'fernanda@example.com', '(11) 96666-4444', 'Av. D, 400'),
('João Pedro', NULL, 'joao@example.com', '(11) 95555-5555', 'Av. E, 500');

-- Pets (8)
INSERT INTO pets (client_id, name, species, breed, birth_date, gender, color, notes) VALUES
(1, 'Rex', 'Cão', 'Vira-lata', '2020-05-10', 'Macho', 'Caramelo', NULL),
(1, 'Mimi', 'Gato', 'Siamês', '2019-03-15', 'Fêmea', 'Branco', NULL),
(2, 'Thor', 'Cão', 'Labrador', '2018-08-01', 'Macho', 'Preto', NULL),
(2, 'Luna', 'Gato', 'Persa', '2021-01-20', 'Fêmea', 'Cinza', NULL),
(3, 'Spike', 'Cão', 'Bulldog', '2017-11-11', 'Macho', 'Marrom', NULL),
(4, 'Belinha', 'Cão', 'Poodle', '2022-02-02', 'Fêmea', 'Branco', NULL),
(5, 'Nina', 'Gato', 'SRD', '2020-12-12', 'Fêmea', 'Tigrada', NULL),
(5, 'Bob', 'Cão', 'Beagle', '2019-09-09', 'Macho', 'Tricolor', NULL);

-- Produtos (10) (inclui alguns serviços)
INSERT INTO products (name, description, price, stock_quantity, min_stock_level, is_service) VALUES
('Consulta Clínica', 'Atendimento clínico geral', 120.00, 0, 0, 1),
('Vacina Antirrábica', 'Dose única', 60.00, 50, 10, 0),
('Vermífugo', 'Comprimidos', 30.00, 100, 20, 0),
('Antibiótico X', '200mg', 45.00, 80, 15, 0),
('Shampoo Medicamentoso', '250ml', 35.00, 40, 10, 0),
('Exame de Sangue', 'Hemograma completo', 90.00, 0, 0, 1),
('Exame de Urina', 'Uroanálise', 70.00, 0, 0, 1),
('Antipulgas', 'Pipeta', 55.00, 60, 10, 0),
('Ração Premium 1kg', 'Cães adultos', 50.00, 30, 5, 0),
('Coleira', 'Coleira simples', 25.00, 25, 5, 0);

-- Agendamentos (5)
-- Considera vets com id 2 e 3
INSERT INTO appointments (pet_id, vet_id, start_time, end_time, room, status, notes, created_by) VALUES
(1, 2, '2025-10-03 09:00:00', '2025-10-03 09:30:00', 'Sala 1', 'agendada', 'Consulta de rotina', 1),
(2, 2, '2025-10-03 10:00:00', '2025-10-03 10:30:00', 'Sala 1', 'agendada', NULL, 1),
(3, 3, '2025-10-03 09:00:00', '2025-10-03 09:45:00', 'Sala 2', 'agendada', NULL, 1),
(4, 3, '2025-10-03 11:00:00', '2025-10-03 11:30:00', 'Sala 2', 'agendada', NULL, 1),
(5, 2, '2025-10-04 09:00:00', '2025-10-04 09:30:00', 'Sala 1', 'agendada', NULL, 1);

-- Movimentações de estoque iniciais (entradas)
INSERT INTO stock_movements (product_id, type, quantity, reason, user_id) VALUES
(2, 'entrada', 50, 'Carga inicial', 1),
(3, 'entrada', 100, 'Carga inicial', 1),
(4, 'entrada', 80, 'Carga inicial', 1),
(5, 'entrada', 40, 'Carga inicial', 1),
(8, 'entrada', 60, 'Carga inicial', 1),
(9, 'entrada', 30, 'Carga inicial', 1),
(10, 'entrada', 25, 'Carga inicial', 1);

-- Vendas (3) com itens e pagamentos
INSERT INTO invoices (client_id, user_id, total, status) VALUES
(1, 1, 140.00, 'paga'),
(2, 1, 85.00, 'paga'),
(3, 1, 50.00, 'paga');

INSERT INTO invoice_items (invoice_id, product_id, quantity, unit_price, subtotal) VALUES
(1, 1, 1, 120.00, 120.00),
(1, 3, 1, 20.00, 20.00),
(2, 8, 1, 55.00, 55.00),
(2, 5, 1, 30.00, 30.00),
(3, 9, 1, 50.00, 50.00);

INSERT INTO payments (invoice_id, method, amount) VALUES
(1, 'dinheiro', 140.00),
(2, 'cartao_credito', 85.00),
(3, 'pix', 50.00);

SET foreign_key_checks = 1;
