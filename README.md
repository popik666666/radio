# radio
<!DOCTYPE html>
<html lang="cs">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Popik Rádio — Live (Firestore)</title>
<style>
  :root{--bg:#070707;--panel:#0f0f10;--muted:#9aa;--accent:#ff4d67}
  *{box-sizing:border-box}
  body{margin:0;font-family:Arial,Helvetica,sans-serif;background:var(--bg);color:#fff;display:flex;justify-content:center;padding:18px}
  .wrap{width:100%;max-width:980px}
  header{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:12px;background:#050506;border-radius:8px}
  header h1{margin:0;color:var(--accent)}
  .box{background:var(--panel);padding:14px;border-radius:10px;margin-top:14px}
  input,button,textarea,select{font-size:14px;padding:8px;border-radius:8px;border:1px solid #222;background:#0d0d0d;color:#fff}
  button{cursor:pointer;background:var(--accent);border:0;color:#fff}
  button.ghost{background:transparent;border:1px solid #333;color:#ddd}
  audio{width:100%;border-radius:8px;background:#000;margin-top:10px}
  #announcementBar{display:none;background:#fff;color:#000;padding:10px;border-radius:8px;margin-top:12px;font-weight:600;text-align:center}
  #gateSection{display:flex;flex-direction:column;gap:8px;align-items:center}
  #mainContent{display:none}
  #chatPanel{display:none;margin-top:12px}
  #messages{height:320px;overflow:auto;background:#080808;padding:10px;border-radius:8px;border:1px solid #141414}
  .msg{padding:6px;border-bottom:1px solid #0f0f0f}
  .meta{font-size:12px;color:var(--muted);margin-bottom:4px}
  .adminTag{color:var(--accent);font-weight:700;margin-right:6px}
  .controls{display:flex;gap:8px;align-items:center;margin-top:8px;flex-wrap:wrap}
  .adminPanel{display:none;margin-top:12px;background:#0d0d0d;padding:12px;border-radius:8px;border:1px solid #222}
  .list{max-height:160px;overflow:auto;background:#080808;padding:8px;border-radius:6px;border:1px solid #202020}
  .small{font-size:13px;color:var(--muted)}
  .blockBtn{background:#b33;padding:6px;border-radius:6px;border:0;color:#fff;cursor:pointer;margin-left:8px}
  .unblockBtn{background:#2a8;padding:6px;border-radius:6px;border:0;color:#000;cursor:pointer;margin-left:8px}
  footer{margin-top:14px;color:#777;font-size:13px;text-align:center}
  @media(max-width:720px){header{flex-direction:column;align-items:flex-start} audio{width:100%}}
</style>
</head>
<body>
  <div class="wrap">
    <header>
      <h1>🎧 Popik Rádio — Live</h1>
      <div class="small">Uzamčeno heslem</div>
    </header>

    <div id="announcementBar">Oznámení</div>

    <div class="box">
      <!-- GATE: bez správného hesla nikdo dál -->
      <div id="gateSection">
        <div class="small">Zadej hlavní přístupový kód (bez něj nejde dál):</div>
        <input id="gateInput" type="password" placeholder="Heslo pro vstup">
        <div class="controls">
          <button id="gateBtn">Odemknout</button>
          <button id="showAdminBtn" class="ghost">Přihlásit jako admin</button>
        </div>

        <div id="adminInline" style="display:none;margin-top:8px;width:100%;max-width:480px">
          <div class="small">Admin heslo:</div>
          <div class="controls">
            <input id="adminPassInline" type="password" placeholder="Admin heslo">
            <button id="adminLoginBtn" class="ghost">Přihlásit admina</button>
          </div>
        </div>
      </div>

      <!-- hlavní obsah (skryté dokud není gate odemčen) -->
      <div id="mainContent">
        <!-- audio player -->
        <div>
          <audio id="audioPlayer" controls preload="none">
            <source id="audioSource" src="https://popik-666.ismyradio.com/listen.mp3" type="audio/mpeg">
            Váš prohlížeč nepodporuje audio.
          </audio>
        </div>

        <!-- chat + info -->
        <div id="chatPanel">
          <div style="display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap;margin-top:12px">
            <div style="flex:1;min-width:260px">
              <div class="small">Chat (po zadání hlavního hesla)</div>
              <div id="messages" aria-live="polite"></div>

              <div class="controls" style="margin-top:8px">
                <input id="nick" type="text" placeholder="Přezdívka" style="flex:0.5">
                <input id="msg" type="text" placeholder="Napiš zprávu..." style="flex:1">
                <button id="sendBtn">Odeslat</button>
              </div>
              <div class="small" style="margin-top:6px">Poznámka: zablokovaní uživatelé mohou poslouchat, ale nemohou psát.</div>
            </div>

            <div style="width:320px;min-width:260px">
              <div class="small">Informační oznámení (admin)</div>
              <div id="annBox" class="list" style="min-height:80px;margin-bottom:8px">Žádná oznámení</div>

              <div class="small" style="margin-top:8px">Aktuálně zablokovaní</div>
              <div id="blockedList" class="list" style="min-height:80px">—</div>
            </div>
          </div>
        </div>

        <!-- admin panel -->
        <div id="adminPanel" class="adminPanel">
          <h3 style="margin:6px 0">⚙️ Admin</h3>

          <div style="margin-bottom:8px">
            <div class="small">Odeslat oznámení (všichni uvidí nahoře):</div>
            <input id="announcementInput" type="text" placeholder="Napiš oznámení..." style="width:100%">
            <div style="margin-top:8px"><button id="sendAnnBtn">Odeslat oznámení</button></div>
          </div>

          <hr style="opacity:.06">

          <div style="margin-top:8px">
            <div class="small">Seznam posledních unikátních uživatelů (admin může blokovat):</div>
            <div id="usersList" class="list" style="min-height:120px">—</div>
          </div>

          <hr style="opacity:.06">

          <div style="margin-top:8px">
            <div class="small">Změna hlavního hesla (vstup):</div>
            <input id="newGate" type="text" placeholder="Nové hlavní heslo">
          </div>
          <div style="margin-top:8px">
            <div class="small">Změna admin hesla:</div>
            <input id="newAdmin" type="text" placeholder="Nové admin heslo">
          </div>
          <div style="margin-top:8px">
            <div class="small">Změna stream URL:</div>
            <input id="newStream" type="text" placeholder="https://.../listen.mp3" style="width:100%">
          </div>

          <div class="controls" style="margin-top:10px">
            <button id="saveBtn">Uložit změny</button>
            <button id="logoutAdminBtn" class="ghost">Odhlásit admina</button>
            <div style="flex:1"></div>
            <div class="small">Admin: <strong id="adminLabel">-</strong></div>
          </div>
        </div>
      </div>
    </div>

    <footer class="small">Popik Rádio — plně na Firestore • admin funkce: blokování/odblokování, oznámení, změny</footer>
  </div>

<!-- Firebase modular SDK -->
<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
  import {
    getFirestore, collection, addDoc, onSnapshot, query, orderBy, serverTimestamp,
    doc, setDoc, getDoc, deleteDoc, getDocs
  } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js";

  // ----- Firebase config (tvé) -----
  const firebaseConfig = {
    apiKey: "AIzaSyBUtnmX7HyiDVbGx9wjfScQQJtwTmrP07k",
    authDomain: "chat-48ab6.firebaseapp.com",
    projectId: "chat-48ab6",
    storageBucket: "chat-48ab6.firebasestorage.app",
    messagingSenderId: "963919446639",
    appId: "1:963919446639:web:246e32fb368aab18ac839a",
    measurementId: "G-C7ZBTEZ1TW"
  };

  const app = initializeApp(firebaseConfig);
  const db = getFirestore(app);

  // ----- Defaults (můžou adminem měnit) -----
  let GATE = localStorage.getItem('popik_gate') || '321456';
  let ADMIN_PASS = localStorage.getItem('popik_admin_pass') || 'p666pr';
  let STREAM = localStorage.getItem('popik_stream') || 'https://popik-666.ismyradio.com/listen.mp3';

  // UI refs
  const gateInput = document.getElementById('gateInput');
  const gateBtn = document.getElementById('gateBtn');
  const showAdminBtn = document.getElementById('showAdminBtn');
  const adminInline = document.getElementById('adminInline');
  const adminPassInline = document.getElementById('adminPassInline');
  const adminLoginBtn = document.getElementById('adminLoginBtn');

  const mainContent = document.getElementById('mainContent');
  const audioPlayer = document.getElementById('audioPlayer');
  const audioSource = document.getElementById('audioSource');

  const chatPanel = document.getElementById('chatPanel');
  const messagesEl = document.getElementById('messages');
  const nickEl = document.getElementById('nick');
  const msgEl = document.getElementById('msg');
  const sendBtn = document.getElementById('sendBtn');

  const adminPanel = document.getElementById('adminPanel');
  const annBox = document.getElementById('annBox');
  const blockedListEl = document.getElementById('blockedList');
  const usersListEl = document.getElementById('usersList');
  const announcementBar = document.getElementById('announcementBar');

  const announcementInput = document.getElementById('announcementInput');
  const sendAnnBtn = document.getElementById('sendAnnBtn');

  const newGate = document.getElementById('newGate');
  const newAdmin = document.getElementById('newAdmin');
  const newStream = document.getElementById('newStream');
  const saveBtn = document.getElementById('saveBtn');
  const logoutAdminBtn = document.getElementById('logoutAdminBtn');
  const adminLabel = document.getElementById('adminLabel');

  // session / identity
  let uid = sessionStorage.getItem('popik_uid');
  if(!uid){ uid = 'u_' + Math.random().toString(36).slice(2,10); sessionStorage.setItem('popik_uid', uid); }
  let nameStored = sessionStorage.getItem('popik_name') || '';

  // runtime state
  let isAdmin = false;
  let messagesUnsub = null;
  let annUnsub = null;
  let blockedUnsub = null;

  // apply initial stream
  function applyStream(){
    audioSource.src = STREAM;
    audioPlayer.load();
  }
  applyStream();

  // --- GATE logic ---
  gateBtn.addEventListener('click', ()=>{
    const v = (gateInput.value||'').trim();
    if(!v) return alert('Zadej heslo pro vstup');
    if(v === GATE){
      // unlock
      document.getElementById('gateSection').style.display = 'none';
      mainContent.style.display = 'block';
      chatPanel.style.display = 'block';
      // set nickname default
      if(!nameStored){
        nameStored = 'U' + Math.random().toString(36).slice(2,6);
        sessionStorage.setItem('popik_name', nameStored);
      }
      nickEl.value = nameStored;
      startRealtime();
    } else {
      alert('Špatné heslo');
    }
  });

  showAdminBtn.addEventListener('click', ()=> {
    adminInline.style.display = adminInline.style.display === 'block' ? 'none' : 'block';
  });

  // inline admin login (from gate)
  adminLoginBtn && adminLoginBtn.addEventListener('click', ()=>{
    const p = (adminPassInline.value||'').trim();
    if(p === ADMIN_PASS){
      isAdmin = true;
      adminPanel.style.display = 'block';
      adminLabel.textContent = 'ADMIN';
      // also unlock gate if not yet unlocked
      document.getElementById('gateSection').style.display = 'none';
      mainContent.style.display = 'block';
      chatPanel.style.display = 'block';
      nickEl.value = 'ADMIN';
      startRealtime();
    } else {
      alert('Špatné admin heslo');
    }
  });

  // --- start realtime listeners ---
  function startRealtime(){
    if(messagesUnsub) return; // already started

    // messages collection
    const q = query(collection(db,'messages'), orderBy('time','asc'));
    messagesUnsub = onSnapshot(q, snap => {
      messagesEl.innerHTML = '';
      const seen = {};
      snap.forEach(d => {
        const data = d.data();
        const div = document.createElement('div');
        div.className = 'msg';
        const meta = document.createElement('div');
        meta.className = 'meta';
        const time = data.time && data.time.toDate ? data.time.toDate().toLocaleTimeString() : '';
        meta.textContent = `[${time}]`;
        const nameSpan = document.createElement('span');
        if(data.admin) nameSpan.className = 'adminTag';
        nameSpan.textContent = (data.name || data.user || 'anon') + ': ';
        const txt = document.createElement('span');
        txt.textContent = data.text || '';
        div.appendChild(meta);
        div.appendChild(nameSpan);
        div.appendChild(txt);

        // admin action (block) for non-admin messages
        if(isAdmin && !data.admin){
          const btn = document.createElement('button');
          btn.className = 'blockBtn';
          btn.textContent = 'Blokovat';
          btn.onclick = ()=> blockUser(data.user || d.id, data.name || data.user);
          div.appendChild(btn);
        }

        messagesEl.appendChild(div);
        messagesEl.scrollTop = messagesEl.scrollHeight;

        // collect users for admin list
        if(data.user) seen[data.user] = data.name || data.user;
      });

      // render users list for admin
      renderUsersList(seen);
    });

    // announcement (doc system/announcement)
    const annDoc = doc(db,'system','announcement');
    annUnsub = onSnapshot(annDoc, snap => {
      if(snap.exists()){
        const data = snap.data();
        announcementBar.textContent = data.text || '';
        announcementBar.style.display = data.text ? 'block' : 'none';
        annBox.innerHTML = '';
        const p = document.createElement('div');
        p.textContent = data.text || '';
        annBox.appendChild(p);
      } else {
        announcementBar.style.display = 'none';
        annBox.innerHTML = 'Žádná oznámení';
      }
    });

    // blocked users listener
    blockedUnsub = onSnapshot(collection(db,'blockedUsers'), snap => {
      blockedListEl.innerHTML = '';
      let any = false;
      snap.forEach(d => {
        any = true;
        const id = d.id;
        const data = d.data();
        const row = document.createElement('div');
        row.className = 'small';
        row.textContent = id + (data?.note ? (' — '+data.note) : '');
        if(isAdmin){
          const unb = document.createElement('button');
          unb.className = 'unblockBtn';
          unb.textContent = 'Odblokovat';
          unb.onclick = ()=> unblockUser(id);
          row.appendChild(unb);
        }
        blockedListEl.appendChild(row);
      });
      if(!any) blockedListEl.textContent = '(žádní zablokovaní)';
      // check if current session user blocked
      getDoc(doc(db,'blockedUsers', uid)).then(snap => {
        const isBlocked = snap.exists();
        if(isBlocked){
          msgEl.disabled = true;
          msgEl.placeholder = 'Jsi zablokován a nemůžeš psát.';
        } else {
          msgEl.disabled = false;
          msgEl.placeholder = 'Napiš zprávu...';
        }
      });
    });
  }

  // send chat message
  sendBtn.addEventListener('click', async ()=>{
    const text = (msgEl.value||'').trim();
    const nick = (nickEl.value||'').trim() || 'Guest';
    if(!text) return;
    // server-side rule should block but check locally
    const blockedSnap = await getDoc(doc(db,'blockedUsers', uid));
    if(blockedSnap.exists()){
      alert('Jsi zablokován a nemůžeš psát.');
      return;
    }
    await addDoc(collection(db,'messages'), {
      user: uid,
      name: nick,
      text: text,
      admin: !!isAdmin,
      time: serverTimestamp()
    });
    msgEl.value = '';
  });

  // admin: send announcement
  sendAnnBtn.addEventListener('click', async ()=>{
    if(!isAdmin) return alert('Jen admin může odesílat oznámení.');
    const t = (announcementInput.value||'').trim();
    if(!t) return;
    await setDoc(doc(db,'system','announcement'), { text: t, time: serverTimestamp(), by: 'admin' });
    announcementInput.value = '';
    alert('Odesláno.');
  });

  // block user
  async function blockUser(userToBlock, name=''){
    if(!isAdmin) return alert('Jen admin může blokovat.');
    if(!userToBlock) return;
    if(!confirm('Chceš zablokovat uživatele ' + (name || userToBlock) + '?')) return;
    await setDoc(doc(db,'blockedUsers', userToBlock), { blocked:true, note: name || '' });
    alert('Zablokováno.');
  }

  // unblock user
  async function unblockUser(userToUnblock){
    if(!isAdmin) return alert('Jen admin může odblokovat.');
    if(!userToUnblock) return;
    if(!confirm('Opravdu odblokovat ' + userToUnblock + '?')) return;
    await deleteDoc(doc(db,'blockedUsers', userToUnblock));
    alert('Odblokováno.');
  }

  // render users list for admin
  function renderUsersList(map){
    usersListEl.innerHTML = '';
    const keys = Object.keys(map||{});
    if(keys.length === 0){ usersListEl.textContent = '(žádní uživatelé)'; return; }
    keys.slice(-200).reverse().forEach(k=>{
      const row = document.createElement('div');
      row.className = 'small';
      row.textContent = `${map[k]} (${k})`;
      const btn = document.createElement('button');
      btn.className = 'blockBtn';
      btn.textContent = 'Blokovat';
      btn.onclick = ()=> blockUser(k, map[k]);
      row.appendChild(btn);
      usersListEl.appendChild(row);
    });
  }

  // save admin settings (change gate, admin pass, stream)
  saveBtn.addEventListener('click', ()=>{
    if(!isAdmin) return alert('Jen admin může ukládat nastavení.');
    const ng = (newGate.value||'').trim();
    const na = (newAdmin.value||'').trim();
    const ns = (newStream.value||'').trim();
    if(ng){ GATE = ng; localStorage.setItem('popik_gate', ng); }
    if(na){ ADMIN_PASS = na; localStorage.setItem('popik_admin_pass', na); }
    if(ns){ STREAM = ns; localStorage.setItem('popik_stream', ns); applyStream(); }
    alert('Uloženo.');
  });

  // admin logout
  logoutAdminBtn.addEventListener('click', ()=>{
    if(!isAdmin) return;
    isAdmin = false;
    adminPanel.style.display = 'none';
    adminLabel.textContent = '-';
    alert('Admin odhlášen.');
  });

  // quick admin login from main UI (button showAdminBtn toggles inline form) is handled above

  // helper: populate admin UI when admin true (expose panel)
  function exposeAdminUI(){
    adminPanel.style.display = isAdmin ? 'block' : 'none';
    if(isAdmin) adminLabel.textContent = 'ADMIN';
  }

  // initial load: apply stored values
  (function init(){
    const sG = localStorage.getItem('popik_gate');
    const sA = localStorage.getItem('popik_admin_pass');
    const sS = localStorage.getItem('popik_stream');
    if(sG) GATE = sG;
    if(sA) ADMIN_PASS = sA;
    if(sS) { STREAM = sS; applyStream(); }
    // if previously admin logged in (not persisted here for security)
  })();

  // cleanup on close
  window.addEventListener('beforeunload', ()=>{
    if(messagesUnsub) messagesUnsub();
    if(annUnsub) annUnsub();
    if(blockedUnsub) blockedUnsub();
  });

</script>
</body>
</html>
