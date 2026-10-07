<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tambah User — Telkom Fallout System</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *{box-sizing:border-box;margin:0;padding:0}
    body{font-family:'Inter',sans-serif;background:#F4F0EE;min-height:100vh;padding:28px}
    .card{max-width:680px;margin:0 auto;background:#fff;border-radius:18px;padding:28px;box-shadow:0 20px 50px -28px rgba(58,4,16,.25)}
    .eyebrow{font-size:10px;font-weight:700;letter-spacing:.08em;color:#C8102E}
    h1{margin-top:7px;font-family:'Space Grotesk',sans-serif;font-size:26px;color:#20161A}
    .sub{margin-top:6px;font-size:12px;color:#7A6B6F;margin-bottom:22px}
    .group{margin-bottom:16px}
    label{display:block;font-size:12px;font-weight:700;margin-bottom:6px;color:#20161A}
    input,select{width:100%;padding:11px 12px;border:1px solid #E7DEDD;border-radius:9px;background:#FBF9F7;font:13px 'Inter',sans-serif}

    .ku-password-wrap{
      position:relative;
      width:100%;
    }

    .ku-password-wrap input{
      width:100%;
      padding-right:44px;
    }

    .ku-password-toggle{
      position:absolute;
      top:50%;
      right:8px;
      transform:translateY(-50%);
      width:30px;
      height:30px;
      padding:0;
      border:0;
      background:transparent;
      color:#8A7D81;
      display:flex;
      align-items:center;
      justify-content:center;
      border-radius:7px;
      cursor:pointer;
      transition:.15s ease;
    }

    .ku-password-toggle:hover{
      color:#C8102E;
      background:rgba(200,16,46,.07);
    }

    .ku-password-toggle:focus-visible{
      outline:2px solid rgba(200,16,46,.28);
      outline-offset:1px;
    }

    .ku-eye-icon{
      width:18px;
      height:18px;
      display:block;
    }

    input:focus,select:focus{outline:none;border-color:#C8102E}
    .error{margin-top:5px;font-size:11px;color:#C8102E}
    .buttons{display:flex;justify-content:flex-end;gap:8px;margin-top:22px}
    .btn{border:0;border-radius:9px;padding:11px 16px;text-decoration:none;font-size:12px;font-weight:700;cursor:pointer}
    .back{background:#F0ECEB;color:#6A6062}.save{background:#C8102E;color:#fff}

    /* =========================================================
       UI ALERT / CONFIRM
       ========================================================= */
    html.ku-create-ui-lock,
    body.ku-create-ui-lock{
      overflow:hidden !important;
    }

    .ku-create-alert-overlay{
      display:none;
      position:fixed !important;
      inset:0 !important;
      width:100vw !important;
      height:100vh !important;
      z-index:2147483000 !important;
      background:rgba(20,16,18,.56);
      backdrop-filter:blur(7px);
      -webkit-backdrop-filter:blur(7px);
      align-items:center;
      justify-content:center;
      padding:16px;
      overflow:hidden !important;
    }

    .ku-create-alert-overlay.open{
      display:flex;
      animation:kuCreateAlertFadeIn .18s ease;
    }

    .ku-create-alert-card{
      width:min(440px, calc(100vw - 32px));
      max-width:440px;
      background:#fff;
      border-radius:22px;
      padding:24px 22px 20px;
      box-shadow:0 34px 90px rgba(0,0,0,.30);
      text-align:center;
      animation:kuCreateAlertPop .18s ease;
    }

    .ku-create-alert-icon{
      width:54px;
      height:54px;
      margin:0 auto 14px;
      border-radius:50%;
      display:flex;
      align-items:center;
      justify-content:center;
      background:rgba(200,16,46,.08);
      color:#C8102E;
    }

    .ku-create-alert-icon.warning{
      background:rgba(216,150,35,.10);
      color:#B86E00;
    }

    .ku-create-alert-icon.error{
      background:rgba(200,16,46,.08);
      color:#C8102E;
    }

    .ku-create-alert-icon svg{
      width:27px;
      height:27px;
      display:block;
      stroke:currentColor;
    }

    .ku-create-alert-card h3{
      margin:0 0 8px;
      font-family:'Space Grotesk',sans-serif;
      font-size:18px;
      font-weight:700;
      color:#252331;
    }

    .ku-create-alert-message{
      min-height:42px;
      margin:0 auto 20px;
      max-width:380px;
      font-size:12.5px;
      line-height:1.65;
      color:#7A808B;
      overflow-wrap:anywhere;
      word-break:break-word;
    }

    .ku-create-alert-message strong{
      color:#2C3442;
      font-weight:800;
    }

    .ku-create-alert-actions{
      display:grid;
      grid-template-columns:1fr 1fr;
      gap:10px;
    }

    .ku-create-alert-actions.single{
      grid-template-columns:1fr;
    }

    .ku-create-alert-btn{
      height:42px;
      border-radius:11px;
      font-family:'Inter',sans-serif;
      font-size:13px;
      font-weight:700;
      cursor:pointer;
      transition:.18s ease;
    }

    .ku-create-alert-cancel{
      border:1px solid #E5E0E1;
      background:#fff;
      color:#6F7177;
    }

    .ku-create-alert-cancel:hover{
      background:#F8F6F6;
    }

    .ku-create-alert-confirm{
      border:1px solid #B40000;
      background:#B40000;
      color:#fff;
      box-shadow:0 10px 22px -14px rgba(180,0,0,.8);
    }

    .ku-create-alert-confirm:hover{
      background:#980000;
      border-color:#980000;
    }

    @keyframes kuCreateAlertFadeIn{
      from{opacity:0}
      to{opacity:1}
    }

    @keyframes kuCreateAlertPop{
      from{opacity:0;transform:translateY(10px) scale(.98)}
      to{opacity:1;transform:translateY(0) scale(1)}
    }

    @media(max-width:520px){
      body{padding:18px}
      .card{padding:22px}
    }
  </style>
</head>
<body>

<div class="card">
  <div class="eyebrow">ADMINISTRASI</div>
  <h1>Tambah User</h1>
  <p class="sub">Buat akun baru untuk pengguna sistem.</p>

  <form id="kuCreateForm" action="{{ route('admin.users.store') }}" method="POST">
    @csrf

    <div class="group">
      <label>Nama</label>
      <input type="text" name="name" value="{{ old('name') }}" required>
      @error('name')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="group">
      <label>Username</label>
      <input type="text" name="username" value="{{ old('username') }}" required>
      @error('username')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="group">
      <label>Email</label>
      <input type="email" name="email" value="{{ old('email') }}" required>
      @error('email')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="group">
      <label>Password</label>
      <div class="ku-password-wrap">
        <input
          type="password"
          name="password"
          id="kuCreatePassword"
          required
        >
        <button
          type="button"
          class="ku-password-toggle"
          data-target="kuCreatePassword"
          aria-label="Tampilkan password"
          title="Tampilkan password"
        >
          <svg class="ku-eye-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="2.7" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </button>
      </div>
      @error('password')<div class="error">{{ $message }}</div>@enderror
    </div>

    <div class="group">
      <label>Konfirmasi Password</label>
      <div class="ku-password-wrap">
        <input
          type="password"
          name="password_confirmation"
          id="kuCreatePasswordConfirmation"
          required
        >
        <button
          type="button"
          class="ku-password-toggle"
          data-target="kuCreatePasswordConfirmation"
          aria-label="Tampilkan konfirmasi password"
          title="Tampilkan konfirmasi password"
        >
          <svg class="ku-eye-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="12" cy="12" r="2.7" stroke="currentColor" stroke-width="1.8"/>
          </svg>
        </button>
      </div>
    </div>

    <div class="group">
      <label>Role</label>
      <select name="role" required>
        <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
      </select>
    </div>

    <div class="buttons">
      <a href="{{ route('admin.users.index') }}" class="btn back">Batal</a>
      <button type="submit" class="btn save" id="kuCreateSaveBtn">Simpan User</button>
    </div>
  </form>
</div>

{{-- =========================================================
     UI ALERT / CONFIRM
     ========================================================= --}}
<div
  class="ku-create-alert-overlay"
  id="kuCreateAlertOverlay"
  aria-hidden="true"
>
  <div
    class="ku-create-alert-card"
    role="dialog"
    aria-modal="true"
    aria-labelledby="kuCreateAlertTitle"
  >
    <div class="ku-create-alert-icon" id="kuCreateAlertIcon"></div>

    <h3 id="kuCreateAlertTitle">Informasi</h3>

    <div class="ku-create-alert-message" id="kuCreateAlertMessage"></div>

    <div class="ku-create-alert-actions" id="kuCreateAlertActions">
      <button type="button" class="ku-create-alert-btn ku-create-alert-cancel" id="kuCreateAlertCancel">
        Batal
      </button>

      <button type="button" class="ku-create-alert-btn ku-create-alert-confirm" id="kuCreateAlertConfirm">
        OK
      </button>
    </div>
  </div>
</div>

<script>
(function () {
  'use strict';

  const form = document.getElementById('kuCreateForm');
  const saveBtn = document.getElementById('kuCreateSaveBtn');
  const overlay = document.getElementById('kuCreateAlertOverlay');
  const icon = document.getElementById('kuCreateAlertIcon');
  const title = document.getElementById('kuCreateAlertTitle');
  const message = document.getElementById('kuCreateAlertMessage');
  const actions = document.getElementById('kuCreateAlertActions');
  const cancelBtn = document.getElementById('kuCreateAlertCancel');
  const confirmBtn = document.getElementById('kuCreateAlertConfirm');

  if (!form || !overlay) return;

  let resolver = null;

  function escapeHtml(value){
    return String(value ?? '')
      .replace(/&/g,'&amp;')
      .replace(/</g,'&lt;')
      .replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;')
      .replace(/'/g,'&#039;');
  }

  function iconHtml(type){
    if(type === 'warning'){
      return `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 3 2.8 20h18.4L12 3Z"/>
          <path d="M12 9v5"/>
          <path d="M12 17.2h.01"/>
        </svg>`;
    }

    return `
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="9"/>
        <path d="M12 8v5"/>
        <path d="M12 16.5h.01"/>
      </svg>`;
  }

  function openAlert({type='warning', titleText='Informasi', messageText='', confirmText='OK', cancelText='Batal', showCancel=true}){
    return new Promise(function(resolve){
      resolver = resolve;

      icon.className = 'ku-create-alert-icon ' + type;
      icon.innerHTML = iconHtml(type);
      title.textContent = titleText;
      message.innerHTML = messageText;
      cancelBtn.textContent = cancelText;
      confirmBtn.textContent = confirmText;
      cancelBtn.style.display = showCancel ? '' : 'none';
      actions.classList.toggle('single', !showCancel);

      document.documentElement.classList.add('ku-create-ui-lock');
      document.body.classList.add('ku-create-ui-lock');
      overlay.classList.add('open');
      overlay.setAttribute('aria-hidden','false');

      setTimeout(function(){ confirmBtn.focus(); }, 20);
    });
  }

  function closeAlert(value){
    overlay.classList.remove('open');
    overlay.setAttribute('aria-hidden','true');
    document.documentElement.classList.remove('ku-create-ui-lock');
    document.body.classList.remove('ku-create-ui-lock');

    if(resolver){
      const resolve = resolver;
      resolver = null;
      resolve(value);
    }
  }

  confirmBtn.addEventListener('click', function(){ closeAlert(true); });
  cancelBtn.addEventListener('click', function(){ closeAlert(false); });

  overlay.addEventListener('click', function(event){
    if(event.target === overlay) closeAlert(false);
  });

  document.addEventListener('keydown', function(event){
    if(event.key === 'Escape' && overlay.classList.contains('open')){
      closeAlert(false);
    }
  });

  form.addEventListener('submit', async function(event){
    event.preventDefault();

    const name = form.querySelector('[name="name"]')?.value?.trim() || '';

    const confirmed = await openAlert({
      type:'warning',
      titleText:'Simpan User?',
      messageText:`Akun <strong>${escapeHtml(name || 'user baru')}</strong> akan dibuat dan disimpan ke sistem.`,
      confirmText:'Simpan',
      cancelText:'Batal',
      showCancel:true
    });

    if(!confirmed) return;

    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';

    HTMLFormElement.prototype.submit.call(form);
  });

  /* =========================================================
     * TOGGLE PASSWORD / KONFIRMASI PASSWORD
     * ========================================================= */

  function eyeIconHtml(showing){
    if(showing){
      return `
        <svg class="ku-eye-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          <path d="M10.6 10.7a2.7 2.7 0 0 0 3.7 3.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
          <path d="M6.7 6.8C4 8.4 2.5 12 2.5 12s3.5 6 9.5 6c1.9 0 3.5-.5 4.9-1.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M9.7 5.1C10.4 4.9 11.2 4.9 12 4.9c6 0 9.5 6 9.5 6s-1.3 2.7-3.8 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>`;
    }

    return `
      <svg class="ku-eye-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="12" cy="12" r="2.7" stroke="currentColor" stroke-width="1.8"/>
      </svg>`;
  }

  document.querySelectorAll('.ku-password-toggle').forEach(function(toggle){
    toggle.addEventListener('click', function(){
      const targetId = toggle.getAttribute('data-target');
      const input = document.getElementById(targetId);

      if(!input) return;

      const showing = input.type === 'text';
      input.type = showing ? 'password' : 'text';

      toggle.innerHTML = eyeIconHtml(!showing);
      toggle.setAttribute(
        'aria-label',
        !showing ? 'Sembunyikan password' : 'Tampilkan password'
      );
      toggle.setAttribute(
        'title',
        !showing ? 'Sembunyikan password' : 'Tampilkan password'
      );
    });
  });

  const serverErrors = @json($errors->all());

  if(Array.isArray(serverErrors) && serverErrors.length > 0){
    setTimeout(function(){
      openAlert({
        type:'error',
        titleText:'Data Belum Benar',
        messageText:serverErrors.map(function(error){
          return `<div>${escapeHtml(error)}</div>`;
        }).join(''),
        confirmText:'OK',
        showCancel:false
      });
    },120);
  }
})();
</script>

</body>
</html>
