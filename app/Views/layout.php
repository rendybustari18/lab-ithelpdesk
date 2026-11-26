<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc($title) ?> · HelpdeskHub</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-yEPZ7jP9S0QKiUQ0m3jjsO4ProIW7wvR9gXkI7JOGa2XVsw53Yke2bsdkBbjmmiX" crossorigin="anonymous">
  <link href="<?= base_url('assets/app.css') ?>" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container"><a class="navbar-brand fw-semibold" href="<?= site_url('/') ?>">HelpdeskHub</a>
    <?php if ($user): ?><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav"><div class="navbar-nav me-auto">
      <?php if (($user['role'] ?? '') === 'Administrator'): ?><a class="nav-link" href="<?= site_url('admin/users') ?>">Users</a><a class="nav-link" href="<?= site_url('admin/categories') ?>">Categories</a>
      <?php else: ?><a class="nav-link" href="<?= site_url('tickets') ?>">Tickets</a><a class="nav-link" href="<?= site_url('reports') ?>">Reports</a><?php endif ?>
    </div><div class="navbar-nav align-items-lg-center"><a class="nav-link" href="<?= site_url('profile') ?>"><?= esc($user['name']) ?> (<?= esc($user['role']) ?>)</a><form action="<?= site_url('logout') ?>" method="post" class="ms-lg-2"><?= csrf_field() ?><button class="btn btn-sm btn-outline-light">Sign out</button></form></div></div><?php endif ?>
  </div>
</nav>
<main class="container py-4">
  <?php foreach (['success', 'error'] as $type): if (session()->getFlashdata($type)): ?><div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?>" role="status"><?= esc(session()->getFlashdata($type)) ?></div><?php endif; endforeach ?>
  <?php foreach ((array) session()->getFlashdata('errors') as $error): ?><div class="alert alert-danger" role="alert"><?= esc($error) ?></div><?php endforeach ?>
  <?= $content ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/js/bootstrap.bundle.min.js" integrity="sha384-WyOxWvTyr1QaMPfiPJSv76iReVthorl5uWlFtZPyK0IEB0hGzo9y5Bp9mYanr8Bu" crossorigin="anonymous" defer></script>
</body></html>
