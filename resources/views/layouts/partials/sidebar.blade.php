<ul class="navbar-nav bg-neon-dark sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{route('dashboard')}}">
        <div class="sidebar-brand-icon rotate-n-15">
           <i class="fas fa-paw text-neon-blue fa-bounce"></i> </div>
        <div class="sidebar-brand-text mx-3 text-neon-blue">PUPCRAWL</div>
    </a>

    <hr class="sidebar-divider my-0 border-neon-glow">

    <li class="nav-item active">
        <a class="nav-link" href="{{route('dashboard')}}">
            <i class="fas fa-chart-line text-neon-green"></i> <span>Dashboard</span></a>
    </li>

    <hr class="sidebar-divider border-neon-glow">

    <li class="nav-item">
        <a class="nav-link" href="{{route('users')}}">
            <i class="fas fa-user-friends text-neon-pink"></i> <span>User List</span></a>
    </li>

    <hr class="sidebar-divider border-neon-glow">

    <li class="nav-item">
        <a class="nav-link" href="{{route('announcements.index')}}">
            <i class="fas fa-broadcast-tower text-neon-yellow"></i> <span>Announcment List</span></a>
    </li>

     <hr class="sidebar-divider border-neon-glow">

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
            aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-inbox text-neon-orange"></i> <span>Notification</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-dark-light py-2 collapse-inner rounded">
                <a class="collapse-item text-white" href="{{route('notifications.index')}}">List</a>
                <a class="collapse-item text-white" href="{{route('notifications.create')}}">Create</a>
            </div>
        </div>
    </li>

     <hr class="sidebar-divider border-neon-glow">

    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseThree"
            aria-expanded="true" aria-controls="collapseThree">
            <i class="fas fa-map-marker-alt text-neon-purple"></i> <span>Dog Friendly</span>
        </a>
        <div id="collapseThree" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-dark-light py-2 collapse-inner rounded">
                <a class="collapse-item text-white" href="{{route('locations.create')}}">List</a>
            </div>
        </div>
    </li>

    <hr class="sidebar-divider d-none d-md-block border-neon-glow">

     <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseff"
            aria-expanded="true" aria-controls="collapseTwo">
           <i class="fas fa-solid fa-mobile-alt"></i><span>Mobile App Settings</span>
        </a>
        <div id="collapseff" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-dark-light py-2 collapse-inner rounded">
                <a class="collapse-item text-white" href="{{route('mobileAppInformation.index')}}">Step By Step Information</a>
                 <a class="collapse-item text-white" href="{{route('mobileAppInformation.pageInfo')}}">Page Information</a>
                  <a class="collapse-item text-white" href="{{route('breads.index')}}">Breed</a>
                  <a class="collapse-item text-white" href="{{route('ageRange.index')}}">Age Range</a>
                  <a class="collapse-item text-white" href="{{route('lookingFor.index')}}">Looking For</a>
                  <a class="collapse-item text-white" href="{{route('vibe.index')}}">Vibe</a>
                  <a class="collapse-item text-white" href="{{route('healthInfo.index')}}">Health Info</a>
                  <a class="collapse-item text-white" href="{{route('travelRadius.index')}}">Travel Radius</a>
                  <a class="collapse-item text-white" href="{{route('availabilityForMeetups.index')}}">Availability for Meetup</a>
            </div>
        </div>

    </li>
 <hr class="sidebar-divider d-none d-md-block border-neon-glow">
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0 bg-neon-blue" id="sidebarToggle"></button>
    </div>

</ul>
<style>
/* 1. KOYU ARKA PLAN VE ANA RENK */
.bg-neon-dark {
    /* Çok Koyu Gri/Siyah Arkaplan */
    background-color: #181a24 !important;
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.5);
}

/* 2. NEON RENKLERİN TANIMI (Glow efekti ile) */
.text-neon-blue { color: #00FFFF !important; text-shadow: 0 0 5px #00FFFF, 0 0 10px #00FFFF; }
.text-neon-green { color: #39FF14 !important; text-shadow: 0 0 5px #39FF14, 0 0 10px #39FF14; }
.text-neon-pink { color: #FF1493 !important; text-shadow: 0 0 5px #FF1493, 0 0 10px #FF1493; }
.text-neon-yellow { color: #FFFF33 !important; text-shadow: 0 0 5px #FFFF33, 0 0 10px #FFFF33; }
.text-neon-orange { color: #FF4500 !important; text-shadow: 0 0 5px #FF4500, 0 0 10px #FF4500; }
.text-neon-purple { color: #BF00FF !important; text-shadow: 0 0 5px #BF00FF, 0 0 10px #BF00FF; }


/* 3. AKTİF ÖĞE VE HOVER VURGUSU */
.sidebar .nav-item .nav-link {
    color: #b2b2b2; /* Menü metni için açık gri */
    transition: all 0.3s ease;
}

.sidebar .nav-item .nav-link:hover {
    background-color: rgba(0, 255, 255, 0.1); /* Neon Mavisi şeffaf hover */
    color: #00FFFF; /* Metin rengini neon mavi yap */
    box-shadow: inset 5px 0 0 #00FFFF; /* Solunda neon mavi çizgi */
    border-radius: 5px;
}

/* Dashboard: Aktif Öğe Vurgusu */
.sidebar .nav-item.active .nav-link {
    background-color: rgba(57, 255, 20, 0.1); /* Neon Yeşil şeffaf arkaplan */
    color: #39FF14; /* Neon Yeşil metin */
    font-weight: bold;
    box-shadow: inset 5px 0 0 #39FF14; /* Solunda neon yeşil çizgi */
    border-radius: 5px;
}

/* 4. AYIRICILAR (Divider) */
.sidebar-divider {
    border-top: 1px solid rgba(178, 178, 178, 0.1); /* Çok soluk çizgi */
}
.border-neon-glow {
    /* Neon Efektli Ayırıcı */
    box-shadow: 0 0 5px rgba(0, 255, 255, 0.5); /* Hafif neon parlaması */
}

/* 5. AÇILIR MENÜ STİLİ */
.bg-dark-light {
    background-color: #2c303a !important; /* Bir ton daha açık koyu */
    border: 1px solid #00FFFF; /* Neon mavi çerçeve */
    box-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
}

.bg-dark-light .collapse-item:hover {
    background-color: #3f444f;
    color: #00FFFF !important;
}

/* 6. PUPCRAWL BRAND (Marka) */
.sidebar-brand-text {
    font-weight: 900;
    letter-spacing: 3px;
}
/* Font Awesome zıplama efekti için CSS varken daha iyi çalışır */
</style>
