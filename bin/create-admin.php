<?php

/**
 * ============================================================================
 * SHALOM DENTAL - Script de Creación de Admin (Seed)
 * ============================================================================
 * Uso: php bin/create-admin.php
 * Descripción: Crea la organización base, la sede matriz y el superadmin.
 * ============================================================================
 */

// 1. Cargar el Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;
use App\Core\Database;

// 2. Inicializar la App (para cargar variables de entorno .env)
$app = Application::getInstance(dirname(__DIR__));

echo "\n🦷 SHALOM DENTAL - Inicialización de Base de Datos\n";
echo "=================================================\n";

try {
    $db = Database::getInstance();
    
    // Iniciar transacción para que se guarde todo o nada
    $db->beginTransaction();

    // --- A. Crear Organización ---
    echo "1. Verificando Organización... ";
    $org = $db->selectOne("SELECT id FROM organizations WHERE ruc = ?", ['1391823721001']);
    
    if ($org) {
        $orgId = $org['id'];
        echo "✅ Ya existe (ID: $orgId)\n";
    } else {
        $orgId = $db->insert('organizations', [
            'ruc' => '1391823721001',
            'business_name' => 'SHALOM DENTAL S.A.',
            'trade_name' => 'Shalom Dental',
            'address' => 'Av. Manabí y Calle Quito, Portoviejo',
            'email' => 'admin@shalomdental.com',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        echo "✅ Creada (ID: $orgId)\n";
    }

    // --- B. Crear Sede Matriz ---
    echo "2. Verificando Sede Matriz... ";
    $loc = $db->selectOne("SELECT id FROM locations WHERE code = ?", ['MATRIZ']);
    
    if ($loc) {
        $locId = $loc['id'];
        echo "✅ Ya existe (ID: $locId)\n";
    } else {
        $locId = $db->insert('locations', [
            'organization_id' => $orgId,
            'code' => 'MATRIZ',
            'name' => 'Matriz Portoviejo',
            'sri_establishment_code' => '001',
            'address' => 'Av. Manabí',
            'city' => 'Portoviejo',
            'province' => 'Manabí',
            'is_active' => 1
        ]);
        echo "✅ Creada (ID: $locId)\n";
    }

    // --- C. Crear Usuario Super Admin ---
    echo "3. Creando Usuario Admin... ";
    
    $email = 'admin@shalomdental.com';
    $password = 'Admin123!';
    
    // Buscar el rol de super_admin (debe existir en tu SQL inicial)
    $role = $db->selectOne("SELECT id FROM roles WHERE code = 'super_admin'");
    
    if (!$role) {
        // Si no existe el rol, lo creamos de emergencia
        $roleId = $db->insert('roles', [
            'code' => 'super_admin', 
            'name' => 'Super Administrador',
            'is_system' => 1
        ]);
    } else {
        $roleId = $role['id'];
    }

    $user = $db->selectOne("SELECT id FROM users WHERE email = ?", [$email]);
    
    if ($user) {
        $userId = $user['id'];
        // Actualizamos la contraseña para asegurar que puedas entrar
        $db->update('users', [
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'role_id' => $roleId // Asegurar que tenga rol admin
        ], 'id = ?', [$userId]);
        echo "✅ Usuario actualizado\n";
    } else {
        $userId = $db->insert('users', [
            'organization_id' => $orgId,
            'role_id' => $roleId,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_BCRYPT),
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        echo "✅ Creado (ID: $userId)\n";
    }

    // --- D. Asignar Sede al Usuario ---
    echo "4. Asignando permisos de sede... ";
    $access = $db->selectOne("SELECT id FROM location_users WHERE user_id = ? AND location_id = ?", [$userId, $locId]);
    
    if (!$access) {
        $db->insert('location_users', [
            'location_id' => $locId,
            'user_id' => $userId,
            'is_primary' => 1
        ]);
        echo "✅ Asignado\n";
    } else {
        echo "✅ Ya tiene acceso\n";
    }

    $db->commit();
    
    echo "\n✨ ¡ÉXITO! USUARIO CREADO CORRECTAMENTE ✨\n";
    echo "--------------------------------------------\n";
    echo "📧 Usuario:  $email\n";
    echo "🔑 Password: $password\n";
    echo "🌍 Login:    http://localhost:8000/login\n\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "\n❌ ERROR FATAL:\n";
    echo $e->getMessage() . "\n";
}