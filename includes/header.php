<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' - MAGANGTRACK' : 'MAGANGTRACK'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #F8FAFC;
            --bg-card: #FFFFFF;
            --border-subtle: #E2E8F0;
            --text-primary: #0F172A;
            --text-secondary: #64748B;
            --primary: #4F46E5;
            --primary-hover: #4338CA;
            --primary-light: #EEF2FF;
            --danger: #EF4444;
            --danger-light: #FEF2F2;
            --success: #10B981;
            --success-light: #ECFDF5;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background-color: var(--bg-main); color: var(--text-primary); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }

        .auth-container { width: 100%; max-width: 440px; background: var(--bg-card); border: 1px solid var(--border-subtle); border-radius: 16px; padding: 32px; box-shadow: 0 4px 20px -2px rgba(0,0,0,0.03); }
        .auth-header { text-align: center; margin-bottom: 28px; }
        .auth-brand { font-size: 20px; font-weight: 700; color: var(--text-primary); display: inline-flex; align-items: center; gap: 8px; margin-bottom: 8px; }
        .auth-brand-dot { width: 8px; height: 8px; background-color: var(--primary); border-radius: 50%; }
        .auth-title { font-size: 22px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px; }
        .auth-subtitle { font-size: 14px; color: var(--text-secondary); }

        .form-group { margin-bottom: 18px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 6px; }
        .form-input, .form-select { width: 100%; padding: 10px 14px; border: 1px solid var(--border-subtle); border-radius: 8px; font-size: 14px; color: var(--text-primary); outline: none; transition: border-color 0.2s; background: #FFF; }
        .form-input:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light); }

        .btn-primary { width: 100%; padding: 11px; background-color: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: background-color 0.2s; }
        .btn-primary:hover { background-color: var(--primary-hover); }

        .alert { padding: 12px 14px; border-radius: 8px; font-size: 13px; font-weight: 500; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
        .alert-danger { background-color: var(--danger-light); color: var(--danger); border: 1px solid #FCA5A5; }
        .alert-success { background-color: var(--success-light); color: var(--success); border: 1px solid #6EE7B7; }

        .auth-footer { margin-top: 24px; text-align: center; font-size: 13px; color: var(--text-secondary); }
        .auth-footer a { color: var(--primary); font-weight: 600; text-decoration: none; }
        .auth-footer a:hover { text-decoration: underline; }
    </style>
</head>
<body></body>