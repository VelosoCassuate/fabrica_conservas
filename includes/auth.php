<?php
/**
 * Sistema de Autenticação para Fábrica de Conservas
 * Controla o acesso às áreas restritas do sistema
 */

require_once 'config.php';
require_once 'database.php';

class Auth {
    private $db;
    private $session_timeout = 3600; // 1 hora em segundos

    public function __construct() {
        $this->db = (new Database())->getConnection();
        $this->startSession();
    }

    /**
     * Inicia a sessão com configurações de segurança
     */
    private function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict'
            ]);
            
            session_start();
            
            // Verificar timeout da sessão
            $this->checkSessionTimeout();
            
            // Regenerar ID da sessão periodicamente
            $this->regenerateSessionId();
        }
    }

    /**
     * Verifica se a sessão expirou
     */
    private function checkSessionTimeout() {
        if (isset($_SESSION['LAST_ACTIVITY']) && 
            (time() - $_SESSION['LAST_ACTIVITY'] > $this->session_timeout)) {
            $this->logout();
            header('Location: /fabrica_conservas/site/cliente_login.php?timeout=1');
            exit;
        }
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    /**
     * Regenera o ID da sessão periodicamente para prevenir fixation attacks
     */
    private function regenerateSessionId() {
        if (!isset($_SESSION['CREATED'])) {
            $_SESSION['CREATED'] = time();
        } else if (time() - $_SESSION['CREATED'] > 1800) { // 30 minutos
            session_regenerate_id(true);
            $_SESSION['CREATED'] = time();
        }
    }

    /**
     * Autentica um cliente pela chave de acesso
     */
    public function authenticateClient($chave_acesso) {
        try {
            $query = "SELECT id, nome, email, chave_acesso FROM clientes WHERE chave_acesso = ? AND ativo = 1";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $chave_acesso);
            $stmt->execute();
            
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($cliente) {
                // Log de acesso bem-sucedido
                $this->logAccess($cliente['id'], 'login_success');
                
                $_SESSION['cliente_id'] = $cliente['id'];
                $_SESSION['cliente_nome'] = $cliente['nome'];
                $_SESSION['cliente_email'] = $cliente['email'];
                $_SESSION['user_type'] = 'cliente';
                $_SESSION['logged_in'] = true;
                
                return true;
            } else {
                // Log de tentativa falha
                $this->logAccess(null, 'login_failed', $chave_acesso);
                return false;
            }
        } catch (PDOException $e) {
            error_log("Erro na autenticação: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Autentica um administrador
     */
    public function authenticateAdmin($username, $password) {
     
        if (isset(ADMIN_USERS[$username]) && password_verify($password, ADMIN_USERS[$username])) {
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_username'] = $username;
            $_SESSION['user_type'] = 'admin';
            $_SESSION['logged_in'] = true;
            
            $this->logAccess(1, 'admin_login_success', $username);
            return true;
        } else {
            $this->logAccess(null, 'admin_login_failed', $username);
            return false;
        }
    }

    /**
     * Verifica se o usuário está autenticado como cliente
     */
    public function isClientLoggedIn() {
        return isset($_SESSION['logged_in']) && 
               $_SESSION['logged_in'] === true && 
               $_SESSION['user_type'] === 'cliente';
    }

    /**
     * Verifica se o usuário está autenticado como administrador
     */
    public function isAdminLoggedIn() {
        return isset($_SESSION['logged_in']) && 
               $_SESSION['logged_in'] === true && 
               $_SESSION['user_type'] === 'admin';
    }

    /**
     * Verifica se há uma sessão ativa de qualquer tipo
     */
    public function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Retorna o tipo de usuário logado
     */
    public function getUserType() {
        return $_SESSION['user_type'] ?? null;
    }

    /**
     * Retorna informações do usuário logado
     */
    public function getUserInfo() {
        if ($this->isClientLoggedIn()) {
            return [
                'id' => $_SESSION['cliente_id'],
                'nome' => $_SESSION['cliente_nome'],
                'email' => $_SESSION['cliente_email'],
                'type' => 'cliente'
            ];
        } elseif ($this->isAdminLoggedIn()) {
            return [
                'id' => $_SESSION['admin_id'],
                'username' => $_SESSION['admin_username'],
                'type' => 'admin'
            ];
        }
        return null;
    }

    /**
     * Faz logout do usuário
     */
    public function logout() {
        if ($this->isLoggedIn()) {
            $userInfo = $this->getUserInfo();
            if ($userInfo) {
                $this->logAccess($userInfo['id'], 'logout');
            }
        }
        
        // Limpa todas as variáveis de sessão
        $_SESSION = array();

        // Destrói a sessão
        if (session_status() == PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        // Limpa o cookie de sessão
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }

    /**
     * Registra tentativas de acesso no sistema
     */
    private function logAccess($user_id, $action, $details = null) {
        try {
            $query = "INSERT INTO log_acessos (user_id, user_type, action, details, ip_address, user_agent) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            
            $user_type = $this->getUserType();
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt->bindParam(1, $user_id);
            $stmt->bindParam(2, $user_type);
            $stmt->bindParam(3, $action);
            $stmt->bindParam(4, $details);
            $stmt->bindParam(5, $ip_address);
            $stmt->bindParam(6, $user_agent);
            
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao registrar log de acesso: " . $e->getMessage());
        }
    }

    /**
     * Redireciona usuário não autenticado
     */
    public function requireAuth($user_type = null) {
        if (!$this->isLoggedIn()) {
            if ($user_type === 'cliente') {
                header('Location: /fabrica_conservas/site/cliente_login.php?error=not_logged_in');
            } elseif ($user_type === 'admin') {
                header('Location: /fabrica_conservas/admin/login.php?error=not_logged_in');
            } else {
                header('Location: /fabrica_conservas/site/index.php');
            }
            exit;
        }

        // Verifica tipo específico de usuário se especificado
        if ($user_type && $this->getUserType() !== $user_type) {
            $this->logout();
            header('Location: /fabrica_conservas/site/index.php?error=unauthorized');
            exit;
        }
    }

    /**
     * Gera uma chave de acesso aleatória para clientes
     */
    public function generateAccessKey($length = 16) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $key;
    }

    /**
     * Altera a chave de acesso de um cliente
     */
    public function changeClientAccessKey($cliente_id, $nova_chave) {
        try {
            $query = "UPDATE clientes SET chave_acesso = ? WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(1, $nova_chave);
            $stmt->bindParam(2, $cliente_id);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Erro ao alterar chave de acesso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica força da senha/chave de acesso
     */
    public function checkPasswordStrength($password) {
        $strength = 0;
        
        // Comprimento mínimo
        if (strlen($password) >= 8) $strength++;
        
        // Contém números
        if (preg_match('/[0-9]/', $password)) $strength++;
        
        // Contém letras minúsculas e maiúsculas
        if (preg_match('/[a-z]/', $password) && preg_match('/[A-Z]/', $password)) $strength++;
        
        // Contém caracteres especiais
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $strength++;
        
        return $strength;
    }
}

// Criar instância global do sistema de autenticação
$auth = new Auth();

/**
 * Funções helper globais para facilitar o uso
 */

function isLoggedIn() {
    global $auth;
    return $auth->isLoggedIn();
}

function isClient() {
    global $auth;
    return $auth->isClientLoggedIn();
}

function isAdmin() {
    global $auth;
    return $auth->isAdminLoggedIn();
}

function getUserInfo() {
    global $auth;
    return $auth->getUserInfo();
}

function requireClientAuth() {
    global $auth;
    $auth->requireAuth('cliente');
}

function requireAdminAuth() {
    global $auth;
    $auth->requireAuth('admin');
}

function logout() {
    global $auth;
    $auth->logout();
}

?>