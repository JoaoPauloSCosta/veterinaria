<?php
require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../helpers/security.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/errors.php';
require_once __DIR__ . '/../helpers/db.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/AppointmentModel.php';
require_once __DIR__ . '/../models/VetProfileModel.php';
require_once __DIR__ . '/../models/SpecialtyModel.php';
require_once __DIR__ . '/../models/VetSpecialtyModel.php';
require_once __DIR__ . '/../helpers/format.php';

class VeterinariansController {
    /**
     * Lista veterinários com filtros, perfil e especialidades
     * Enriquece dados para a view e renderiza lista
     */
    public static function index(): void {
        require_login();
        require_role(['admin','recepcao']);
        $q = trim($_GET['q'] ?? '');
        [$vets, $total] = UserModel::paginateByRole('veterinario', $q, 100, 0);
        $specialties = SpecialtyModel::listAll();
        foreach ($vets as &$v) {
            $profile = VetProfileModel::findByUserId((int)$v['id']);
            if ($profile) { $v = array_merge($v, $profile); }
            // Especialidades vinculadas
            $ids = VetSpecialtyModel::listIdsByVet((int)$v['id']);
            $v['specialty_ids'] = $ids;
            $v['specialties'] = VetSpecialtyModel::listNamesByVet((int)$v['id']);
        }

        // Usar sessão para mensagens de sucesso, mantendo URL limpa
        $flash_success = $_SESSION['vet_success'] ?? null;
        if ($flash_success !== null) { unset($_SESSION['vet_success']); }

        // Consumir (e limpar) dados de conflito (agendamentos futuros) vindos via sessão
        $futureAppointments = $_SESSION['vet_conflict_appointments'] ?? [];
        $conflict_vet_id = $_SESSION['vet_conflict_id'] ?? null;
        $conflict_vet_name = $_SESSION['vet_conflict_name'] ?? null;
        $showConflictModal = !empty($_SESSION['vet_conflict_show']);
        unset($_SESSION['vet_conflict_appointments'], $_SESSION['vet_conflict_id'], $_SESSION['vet_conflict_name'], $_SESSION['vet_conflict_show']);

        render('veterinarians/index', compact('vets','q','flash_success','specialties','futureAppointments','conflict_vet_id','conflict_vet_name','showConflictModal'));
    }

    /**
     * Cria novo veterinário, perfil e especialidades
     * Valida entradas, garante email único e registra auditoria
     */
    public static function create(): void {
        require_login();
        require_role(['admin']);
        csrf_validate();
        $name = sanitize_string($_POST['name'] ?? '');
        $email = sanitize_string($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $isActive = !empty($_POST['is_active']) ? 1 : 0;
        // Perfil
        $mobile = sanitize_string($_POST['mobile_phone'] ?? '');
        $landline = sanitize_string($_POST['landline_phone'] ?? '');
        $pemail = sanitize_string($_POST['professional_email'] ?? '');
        $crmv = sanitize_string($_POST['crmv'] ?? '');
        $crmvUf = strtoupper(sanitize_string($_POST['crmv_uf'] ?? ''));
        $employment = sanitize_string($_POST['employment_type'] ?? '');
        $admission = sanitize_string($_POST['admission_date'] ?? '');
        $salary = sanitize_string($_POST['salary'] ?? '');
        $workload = (int)($_POST['workload_hours'] ?? 0);

        // Convert BR date to ISO if needed
        if (!empty($admission)) {
            if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $admission)) {
                $iso = br_to_iso_date($admission);
                $admission = $iso ?? '';
            }
        }

        if ($name === '' || !validate_email($email) || strlen($password) < 6) {
            $flash_error = 'Dados inválidos. Verifique os campos.';
            [$vets] = UserModel::paginateByRole('veterinario', '', 100, 0);
            // Enriquecer com perfil/especialidades para a view mesmo em erro
            $specialties = SpecialtyModel::listAll();
            foreach ($vets as &$vv) {
                $prof = VetProfileModel::findByUserId((int)$vv['id']);
                if ($prof) { $vv = array_merge($vv, $prof); }
                $vv['specialty_ids'] = VetSpecialtyModel::listIdsByVet((int)$vv['id']);
                $vv['specialties'] = VetSpecialtyModel::listNamesByVet((int)$vv['id']);
            }
            render('veterinarians/index', compact('vets','flash_error','specialties'));
            return;
        }

        $pdo = DB::getConnection();
        // Email único
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email'=>$email]);
        if ($stmt->fetch()) {
            $flash_error = 'Email já cadastrado.';
            [$vets] = UserModel::paginateByRole('veterinario', '', 100, 0);
            // Enriquecer com perfil/especialidades para a view mesmo em erro
            $specialties = SpecialtyModel::listAll();
            foreach ($vets as &$vv) {
                $prof = VetProfileModel::findByUserId((int)$vv['id']);
                if ($prof) { $vv = array_merge($vv, $prof); }
                $vv['specialty_ids'] = VetSpecialtyModel::listIdsByVet((int)$vv['id']);
                $vv['specialties'] = VetSpecialtyModel::listNamesByVet((int)$vv['id']);
            }
            render('veterinarians/index', compact('vets','flash_error','specialties'));
            return;
        }

        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $id = UserModel::createVet(['name'=>$name,'email'=>$email,'password_hash'=>$hash,'is_active'=>$isActive]);
            $profile = [
                'mobile_phone' => $mobile ?: null,
                'landline_phone' => $landline ?: null,
                'professional_email' => ($pemail && validate_email($pemail)) ? $pemail : null,
                'crmv' => $crmv ?: null,
                'crmv_uf' => ($crmvUf !== '') ? $crmvUf : null,
                'employment_type' => in_array($employment, ['CLT','PJ'], true) ? $employment : null,
                'admission_date' => $admission ?: null,
                'salary' => $salary !== '' ? (float)$salary : null,
                'workload_hours' => $workload > 0 ? $workload : null,
            ];
            VetProfileModel::upsert($id, $profile);
            // Especialidades
            $postedS = $_POST['specialties'] ?? [];
            $postedIds = [];
            foreach ((array)$postedS as $sid) { $sidI = (int)$sid; if ($sidI > 0) { $postedIds[] = $sidI; } }
            // Filtrar apenas IDs válidos existentes
            $allowed = array_map(static fn($r) => (int)$r['id'], SpecialtyModel::listAll());
            $allowedSet = array_flip($allowed);
            $finalIds = array_values(array_filter($postedIds, static fn($sid) => isset($allowedSet[$sid])));
            VetSpecialtyModel::replaceForVet($id, $finalIds);
            audit_log($_SESSION['user']['id'] ?? null, 'vet_create', 'users', $id, json_encode(['name'=>$name,'email'=>$email,'is_active'=>$isActive]));
            $_SESSION['vet_success'] = 'Veterinário cadastrado com sucesso';
            header('Location: ' . APP_URL . '/veterinarians');
        } catch (Throwable $e) {
            $flash_error = friendly_pdo_message($e, 'veterinário');
            [$vets] = UserModel::paginateByRole('veterinario', '', 100, 0);
            // Enriquecer com perfil/especialidades para a view mesmo em erro
            $specialties = SpecialtyModel::listAll();
            foreach ($vets as &$vv) {
                $prof = VetProfileModel::findByUserId((int)$vv['id']);
                if ($prof) { $vv = array_merge($vv, $prof); }
                $vv['specialty_ids'] = VetSpecialtyModel::listIdsByVet((int)$vv['id']);
                $vv['specialties'] = VetSpecialtyModel::listNamesByVet((int)$vv['id']);
            }
            render('veterinarians/index', compact('vets','flash_error','specialties'));
        }
    }

    /**
     * Atualiza dados do veterinário, perfil e especialidades
     * Valida entradas e email único; registra auditoria
     */
    public static function edit(int $id): void {
        require_login();
        require_role(['admin']);
        csrf_validate();
        $name = sanitize_string($_POST['name'] ?? '');
        $email = sanitize_string($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $isActive = !empty($_POST['is_active']) ? 1 : 0;
        // Perfil
        $mobile = sanitize_string($_POST['mobile_phone'] ?? '');
        $landline = sanitize_string($_POST['landline_phone'] ?? '');
        $pemail = sanitize_string($_POST['professional_email'] ?? '');
        $crmv = sanitize_string($_POST['crmv'] ?? '');
        $crmvUf = strtoupper(sanitize_string($_POST['crmv_uf'] ?? ''));
        $employment = sanitize_string($_POST['employment_type'] ?? '');
        $admission = sanitize_string($_POST['admission_date'] ?? '');
        $salary = sanitize_string($_POST['salary'] ?? '');
        $workload = (int)($_POST['workload_hours'] ?? 0);

        // Convert BR date to ISO if needed
        if (!empty($admission)) {
            if (preg_match('#^\d{2}/\d{2}/\d{4}$#', $admission)) {
                $iso = br_to_iso_date($admission);
                $admission = $iso ?? '';
            }
        }

        if ($name === '' || !validate_email($email) || ($password !== '' && strlen($password) < 6)) {
            $flash_error = 'Dados inválidos. Verifique os campos.';
            [$vets] = UserModel::paginateByRole('veterinario', '', 100, 0);
            render('veterinarians/index', compact('vets','flash_error'));
            return;
        }

        $pdo = DB::getConnection();
        // Email único exceto o próprio
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id <> :id LIMIT 1');
        $stmt->execute([':email'=>$email, ':id'=>$id]);
        if ($stmt->fetch()) {
            $flash_error = 'Email já cadastrado para outro usuário.';
            [$vets] = UserModel::paginateByRole('veterinario', '', 100, 0);
            render('veterinarians/index', compact('vets','flash_error'));
            return;
        }

        try {
            $data = ['name'=>$name,'email'=>$email,'is_active'=>$isActive];
            if ($password !== '') { $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT); }
            UserModel::updateVet($id, $data);
            $profile = [
                'mobile_phone' => $mobile ?: null,
                'landline_phone' => $landline ?: null,
                'professional_email' => ($pemail && validate_email($pemail)) ? $pemail : null,
                'crmv' => $crmv ?: null,
                'crmv_uf' => ($crmvUf !== '') ? $crmvUf : null,
                'employment_type' => in_array($employment, ['CLT','PJ'], true) ? $employment : null,
                'admission_date' => $admission ?: null,
                'salary' => $salary !== '' ? (float)$salary : null,
                'workload_hours' => $workload > 0 ? $workload : null,
            ];
            VetProfileModel::upsert($id, $profile);
            // Especialidades
            $postedS = $_POST['specialties'] ?? [];
            $postedIds = [];
            foreach ((array)$postedS as $sid) { $sidI = (int)$sid; if ($sidI > 0) { $postedIds[] = $sidI; } }
            // Filtrar apenas IDs válidos existentes
            $allowed = array_map(static fn($r) => (int)$r['id'], SpecialtyModel::listAll());
            $allowedSet = array_flip($allowed);
            $finalIds = array_values(array_filter($postedIds, static fn($sid) => isset($allowedSet[$sid])));
            VetSpecialtyModel::replaceForVet($id, $finalIds);
            audit_log($_SESSION['user']['id'] ?? null, 'vet_update', 'users', $id, json_encode(['name'=>$name,'email'=>$email,'is_active'=>$isActive]));
            $_SESSION['vet_success'] = 'Veterinário atualizado com sucesso';
            header('Location: ' . APP_URL . '/veterinarians');
        } catch (Throwable $e) {
            $flash_error = friendly_pdo_message($e, 'veterinário');
            [$vets] = UserModel::paginateByRole('veterinario', '', 100, 0);
            render('veterinarians/index', compact('vets','flash_error'));
        }
    }

    /**
     * Exclui veterinário (e perfil) com proteção CSRF
     * Registra auditoria e retorna à listagem com status
     */
    public static function delete(int $id): void {
        require_login();
        require_role(['admin']);
        csrf_validate();
        try {
            // 1) Inativar o veterinário
            UserModel::setActive($id, false);

            // 2) Verificar agendamentos futuros
            $futureAppointments = AppointmentModel::listFutureByVet($id);
            if (!empty($futureAppointments)) {
                // 3) Tratamento de conflitos: redirecionar e usar sessão (flash) para abrir modal apenas uma vez
                $_SESSION['vet_conflict_appointments'] = $futureAppointments;
                $_SESSION['vet_conflict_id'] = $id;
                $vet = UserModel::findById($id);
                $_SESSION['vet_conflict_name'] = $vet['name'] ?? null;
                $_SESSION['vet_conflict_show'] = 1; // acionar modal somente após tentativa de exclusão
                header('Location: ' . APP_URL . '/veterinarians');
                return;
            }

            // 4) Sem conflitos: permitir exclusão definitiva
            UserModel::deleteVet($id);
            audit_log($_SESSION['user']['id'] ?? null, 'vet_delete', 'users', $id);
            $_SESSION['vet_success'] = 'Veterinário excluído com sucesso';
            header('Location: ' . APP_URL . '/veterinarians');
        } catch (Throwable $e) {
            $flash_error = friendly_pdo_message($e, 'veterinário');
            [$vets] = UserModel::paginateByRole('veterinario', '', 100, 0);
            $specialties = SpecialtyModel::listAll();
            render('veterinarians/index', compact('vets','flash_error','specialties'));
        }
    }
}