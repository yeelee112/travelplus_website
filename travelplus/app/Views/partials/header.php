<!-- <header class="bg-white border-bottom">
    <div class="container py-3 d-flex justify-content-between align-items-center">
        <a href="<?= site_url() ?>" class="logo-header"><img src="<?= base_url('assets/images/logo.svg') ?>" alt="Travel Plus" /></a>
    <nav class="main-nav d-none d-lg-flex gap-4">
        <a href="<?= site_url() ?>" class="nav-link"><?= lang('Frontend.home') ?></a>
        <a href="#" class="nav-link"><?= lang('Frontend.tours') ?></a>
        <a href="#" class="nav-link"><?= lang('Frontend.destination') ?></a>
        <a href="#" class="nav-link"><?= lang('Frontend.about') ?></a>
        <a href="#" class="nav-link"><?= lang('Frontend.contact') ?></a>
    </nav>

    <div class="locale-switcher">
        <a href="<?= site_url('en') ?>" <?= service('request')->getLocale() === 'en' ? 'class="active"' : '' ?>>EN</a>
        <a href="<?= site_url('vi') ?>" <?= service('request')->getLocale() === 'vi' ? 'class="active"' : '' ?>>VN</a>
    </div>
    </div>
</header> -->


<div class="topbar-area two d-lg-block d-none">
        <div class="container-fluid">
            <div class="topbar-wrap">
                <ul class="contact-list">
                    <li class="single-contact">
                        <div class="icon"><svg width="16" height="16" viewBox="0 0 16 16"
                                xmlns="http://www.w3.org/2000/svg">
                                <g>
                                    <path
                                        d="M15.5645 11.7424L13.3317 9.50954C12.5342 8.7121 11.1786 9.03111 10.8596 10.0678C10.6204 10.7855 9.82291 11.1842 9.10521 11.0247C7.51032 10.626 5.35722 8.55261 4.9585 6.87797C4.71926 6.16024 5.19773 5.36279 5.91543 5.12359C6.95211 4.80461 7.27109 3.44895 6.47364 2.65151L4.2408 0.418659C3.60284 -0.139553 2.6459 -0.139553 2.08769 0.418659L0.572545 1.93381C-0.942601 3.5287 0.732035 7.75516 4.48003 11.5032C8.22802 15.2512 12.4545 17.0056 14.0494 15.4106L15.5645 13.8955C16.1228 13.2575 16.1228 12.3006 15.5645 11.7424Z">
                                    </path>
                                </g>
                            </svg></div><a href="tel:91345533865">+91 345 533 865</a>
                    </li>
                    <li class="single-contact">
                        <div class="icon"><svg width="16" height="16" viewBox="0 0 16 16"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M1.96372 3.07414L6.28622 7.39851C7.22897 8.33945 8.77003 8.34026 9.71356 7.39851L14.0361 3.07414C14.0463 3.06398 14.0541 3.05169 14.0591 3.03815C14.064 3.02461 14.0659 3.01015 14.0647 2.9958C14.0634 2.98144 14.059 2.96754 14.0517 2.95508C14.0445 2.94262 14.0346 2.93191 14.0227 2.9237C13.5819 2.61623 13.0455 2.43395 12.4677 2.43395H3.53216C2.95431 2.43395 2.41791 2.61626 1.97703 2.9237C1.96519 2.93191 1.95529 2.94262 1.94805 2.95508C1.9408 2.96754 1.93639 2.98144 1.93512 2.9958C1.93385 3.01015 1.93575 3.02461 1.9407 3.03815C1.94564 3.05169 1.9535 3.06398 1.96372 3.07414ZM0.808595 5.15748C0.808243 4.7181 0.915024 4.28525 1.11969 3.89645C1.12683 3.88274 1.13711 3.87091 1.14969 3.86193C1.16226 3.85294 1.17678 3.84705 1.19207 3.84473C1.20735 3.84241 1.22296 3.84372 1.23764 3.84857C1.25232 3.85342 1.26564 3.86167 1.27653 3.87264L5.54431 8.14042C6.89578 9.49385 9.10322 9.49464 10.4555 8.14042L14.7233 3.87264C14.7342 3.86167 14.7475 3.85342 14.7622 3.84857C14.7769 3.84372 14.7925 3.84241 14.8077 3.84473C14.823 3.84705 14.8376 3.85294 14.8501 3.86193C14.8627 3.87091 14.873 3.88274 14.8801 3.89645C15.0848 4.28526 15.1916 4.7181 15.1912 5.15748V10.843C15.1912 12.3459 13.9687 13.5666 12.4677 13.5666H3.53216C2.03116 13.5666 0.808595 12.3459 0.808595 10.843V5.15748Z">
                                </path>
                            </svg></div><a href="mailto:info@example.com">info@example.com</a>
                    </li>
                </ul><a class="header-logo" href="../"><img alt="" loading="lazy" width="550" height="220"
                        decoding="async" data-nimg="1" style="color:transparent"
                        src="<?= base_url('assets/images/logo.svg') ?>"></a>
                <div class="topbar-right">
                    <div class="support-and-language-area"><a href="#">Need Help?</a>
                        <div class="language-area">
                            <div class="language-btn"><svg width="14" height="14" viewBox="0 0 14 14"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M7 14C5.13023 14 3.37239 13.2719 2.05023 11.9498C0.728137 10.6276 0 8.86977 0 7C0 5.13023 0.728137 3.37239 2.05023 2.05023C3.37239 0.728137 5.13023 0 7 0C8.86977 0 10.6276 0.728137 11.9498 2.05023C13.2719 3.37239 14 5.13023 14 7C14 8.86977 13.2719 10.6276 11.9498 11.9498C10.6276 13.2719 8.86977 14 7 14ZM7 0.583324C3.46183 0.583324 0.583324 3.46183 0.583324 7C0.583324 10.5382 3.46183 13.4166 7 13.4166C10.5382 13.4166 13.4166 10.5382 13.4166 7C13.4166 3.46183 10.5382 0.583324 7 0.583324Z">
                                        </path>
                                        <path
                                            d="M7 14C5.90297 14 4.8854 13.2486 4.13468 11.8841C3.41431 10.5747 3.01758 8.84018 3.01758 7C3.01758 5.15982 3.41431 3.42527 4.13468 2.11589C4.8854 0.751433 5.90297 0 7 0C8.09704 0 9.11461 0.751433 9.8653 2.11589C10.5857 3.42527 10.9824 5.15982 10.9824 7C10.9824 8.84018 10.5857 10.5747 9.8653 11.8841C9.11461 13.2486 8.09704 14 7 14ZM7 0.583324C6.12536 0.583324 5.2893 1.22746 4.64579 2.39709C3.97198 3.62179 3.6009 5.25645 3.6009 7C3.6009 8.74355 3.97198 10.3782 4.64576 11.6029C5.28927 12.7725 6.12533 13.4166 6.99998 13.4166C7.87462 13.4166 8.71068 12.7725 9.35419 11.6029C10.028 10.3782 10.3991 8.74355 10.3991 7C10.3991 5.25645 10.028 3.62179 9.35419 2.39709C8.71071 1.22746 7.87462 0.583324 7 0.583324Z">
                                        </path>
                                        <path
                                            d="M6.99968 13.9573C6.8386 13.9573 6.70801 13.8267 6.70801 13.6657V0.334156C6.70801 0.173074 6.83857 0.0424805 6.99968 0.0424805C7.16077 0.0424805 7.29136 0.173074 7.29136 0.334156V13.6657C7.29136 13.8267 7.16077 13.9573 6.99968 13.9573Z">
                                        </path>
                                        <path
                                            d="M13.6661 7.29147H0.334644C0.173562 7.29147 0.0429688 7.16088 0.0429688 6.99979C0.0429688 6.83871 0.173562 6.70812 0.334644 6.70812H13.6661C13.8272 6.70812 13.9578 6.83868 13.9578 6.99979C13.9578 7.16088 13.8272 7.29147 13.6661 7.29147ZM12.7022 3.81187H1.29862C1.13754 3.81187 1.00695 3.6813 1.00695 3.52019C1.00695 3.35908 1.13751 3.22852 1.29862 3.22852H12.7022C12.8633 3.22852 12.9939 3.35908 12.9939 3.52019C12.9939 3.6813 12.8632 3.81187 12.7022 3.81187ZM12.7022 10.771H1.29862C1.13754 10.771 1.00695 10.6404 1.00695 10.4794C1.00695 10.3183 1.13751 10.1877 1.29862 10.1877H12.7022C12.8633 10.1877 12.9939 10.3183 12.9939 10.4794C12.9939 10.6404 12.8632 10.771 12.7022 10.771Z">
                                        </path>
                                    </g>
                                </svg><span>EN</span><i class="bi bi-caret-down-fill"></i></div>
                            <ul class="language-list ">
                                <li><a href="#"><img alt="" loading="lazy" width="18" height="18" decoding="async"
                                            data-nimg="1" style="color:transparent"
                                            src="<?= base_url('assets/images/home/en-us.svg') ?>">English</a>
                                </li>
                                <li><a href="#"><img alt="" loading="lazy" width="18" height="18" decoding="async"
                                            data-nimg="1" style="color:transparent"
                                            src="<?= base_url('assets/images/home/vi-vn.svg') ?>">Việt Nam</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="search-and-login">
                        <div class="search-bar">
                            <div class="search-btn" data-toggle="dropdown" data-target=".search-input"><svg width="16" height="16" viewBox="0 0 16 16"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M15.7417 14.6098L13.486 12.3621C14.7088 10.8514 15.3054 8.9291 15.1526 6.99153C14.9998 5.05396 14.1093 3.24888 12.6648 1.94851C11.2203 0.648146 9.33193 -0.0483622 7.38901 0.00261294C5.44609 0.0535881 3.59681 0.84816 2.22248 2.22248C0.84816 3.59681 0.0535881 5.44609 0.00261294 7.38901C-0.0483622 9.33193 0.648146 11.2203 1.94851 12.6648C3.24888 14.1093 5.05396 14.9998 6.99153 15.1526C8.9291 15.3054 10.8514 14.7088 12.3621 13.486L14.6098 15.7417C14.6839 15.8164 14.7721 15.8757 14.8692 15.9161C14.9664 15.9566 15.0705 15.9774 15.1758 15.9774C15.281 15.9774 15.3852 15.9566 15.4823 15.9161C15.5794 15.8757 15.6676 15.8164 15.7417 15.7417C15.8164 15.6676 15.8757 15.5794 15.9161 15.4823C15.9566 15.3852 15.9774 15.281 15.9774 15.1758C15.9774 15.0705 15.9566 14.9664 15.9161 14.8692C15.8757 14.7721 15.8164 14.6839 15.7417 14.6098ZM1.62572 7.60368C1.62572 6.42135 1.97632 5.26557 2.63319 4.2825C3.29005 3.29943 4.22368 2.53322 5.31601 2.08076C6.40834 1.62831 7.61031 1.50992 8.76992 1.74058C9.92953 1.97124 10.9947 2.54059 11.8307 3.37662C12.6668 4.21266 13.2361 5.27783 13.4668 6.43744C13.6974 7.59705 13.579 8.79902 13.1266 9.89134C12.6741 10.9837 11.9079 11.9173 10.9249 12.5742C9.94178 13.231 8.78601 13.5816 7.60368 13.5816C6.01822 13.5816 4.49771 12.9518 3.37662 11.8307C2.25554 10.7096 1.62572 9.18913 1.62572 7.60368Z">
                                        </path>
                                    </g>
                                </svg></div>
                            <div class="search-input ">
                                <div class="search-close"></div>
                                <form>
                                    <div class="search-group">
                                        <div class="form-inner2"><input type="text"
                                                placeholder="Find Your Perfect Tour Package"><button type="submit"><i
                                                    class="bi bi-search"></i></button></div>
                                    </div>
                                    <div class="quick-search">
                                        <ul>
                                            <li>Quick Search :</li>
                                            <li><a href="../travel-package/">Thailand Tour,</a></li>
                                            <li><a href="../travel-package/">Philippines Tour,</a></li>
                                            <li><a href="../travel-package/">Bali Tour,</a></li>
                                            <li><a href="../travel-package/">Hawaii, USA Tour,</a></li>
                                            <li><a href="../travel-package/">Switzerland Tour,</a></li>
                                            <li><a href="../travel-package/">Maldives Tour,</a></li>
                                            <li><a href="../travel-package/">Paris Tour,</a></li>
                                        </ul>
                                    </div>
                                </form>
                            </div>
                        </div><a href="#" class="primary-btn1 three black-bg"><span><svg width="15" height="15"
                                    viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M7.50105 7.78913C9.64392 7.78913 11.3956 6.03744 11.3956 3.89456C11.3956 1.75169 9.64392 0 7.50105 0C5.35818 0 3.60652 1.75169 3.60652 3.89456C3.60652 6.03744 5.35821 7.78913 7.50105 7.78913ZM14.1847 10.9014C14.0827 10.6463 13.9467 10.4082 13.7936 10.1871C13.0113 9.0306 11.8038 8.2653 10.4433 8.07822C10.2732 8.06123 10.0861 8.09522 9.95007 8.19727C9.23578 8.72448 8.38546 8.99658 7.50108 8.99658C6.61671 8.99658 5.76638 8.72448 5.05209 8.19727C4.91603 8.09522 4.72895 8.04421 4.5589 8.07822C3.19835 8.2653 1.97387 9.0306 1.20857 10.1871C1.05551 10.4082 0.919443 10.6633 0.817424 10.9014C0.766415 11.0034 0.783407 11.1225 0.834416 11.2245C0.970484 11.4626 1.14054 11.7007 1.2936 11.9048C1.53168 12.2279 1.78679 12.517 2.07592 12.7891C2.31401 13.0272 2.58611 13.2483 2.85824 13.4694C4.20177 14.4728 5.81742 15 7.48409 15C9.15076 15 10.7664 14.4728 12.1099 13.4694C12.382 13.2653 12.6541 13.0272 12.8923 12.7891C13.1644 12.517 13.4365 12.2279 13.6746 11.9048C13.8446 11.6837 13.9977 11.4626 14.1338 11.2245C14.2188 11.1225 14.2358 11.0034 14.1847 10.9014Z">
                                        </path>
                                    </g>
                                </svg>Login</span><span><svg width="15" height="15" viewBox="0 0 15 15"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M7.50105 7.78913C9.64392 7.78913 11.3956 6.03744 11.3956 3.89456C11.3956 1.75169 9.64392 0 7.50105 0C5.35818 0 3.60652 1.75169 3.60652 3.89456C3.60652 6.03744 5.35821 7.78913 7.50105 7.78913ZM14.1847 10.9014C14.0827 10.6463 13.9467 10.4082 13.7936 10.1871C13.0113 9.0306 11.8038 8.2653 10.4433 8.07822C10.2732 8.06123 10.0861 8.09522 9.95007 8.19727C9.23578 8.72448 8.38546 8.99658 7.50108 8.99658C6.61671 8.99658 5.76638 8.72448 5.05209 8.19727C4.91603 8.09522 4.72895 8.04421 4.5589 8.07822C3.19835 8.2653 1.97387 9.0306 1.20857 10.1871C1.05551 10.4082 0.919443 10.6633 0.817424 10.9014C0.766415 11.0034 0.783407 11.1225 0.834416 11.2245C0.970484 11.4626 1.14054 11.7007 1.2936 11.9048C1.53168 12.2279 1.78679 12.517 2.07592 12.7891C2.31401 13.0272 2.58611 13.2483 2.85824 13.4694C4.20177 14.4728 5.81742 15 7.48409 15C9.15076 15 10.7664 14.4728 12.1099 13.4694C12.382 13.2653 12.6541 13.0272 12.8923 12.7891C13.1644 12.517 13.4365 12.2279 13.6746 11.9048C13.8446 11.6837 13.9977 11.4626 14.1338 11.2245C14.2188 11.1225 14.2358 11.0034 14.1847 10.9014Z">
                                        </path>
                                    </g>
                                </svg>Login</span></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <header class="style-1 two">
        <div class="container d-flex flex-nowrap align-items-center justify-content-lg-center justify-content-between">
            <a class="header-logo d-lg-none d-block" href="../"><img alt="" loading="lazy" width="550" height="220"
                    decoding="async" data-nimg="1" style="color:transparent" src="<?= base_url('assets/images/logo.svg') ?>"></a>
            <div class="main-menu ">
                <div class="mobile-logo-area d-lg-none d-flex align-items-center justify-content-between"><a
                        class="mobile-logo-wrap" href="../"><img alt="" loading="lazy" width="550" height="220"
                            decoding="async" data-nimg="1" style="color:transparent"
                            src="../assets/img/header-logo2.svg"></a>
                    <div class="menu-close-btn"><i class="bi bi-x"></i></div>
                </div>
                <ul class="menu-list">
                    <li class="menu-item-has-children  active"><a class="drop-down" href="../">Home<i
                                class="bi bi-caret-down-fill"></i></a><i class="bi bi-plus dropdown-icon"></i>
                        <ul class="sub-menu none">
                            <li><a href="../">Main Home</a></li>
                            <li class=""><a href="../travel-agency-01/">Travel Agency-01</a></li>
                            <li class=""><a href="../travel-agency-02/">Travel Agency-02</a></li>
                            <li class=""><a href="../travel-agency-03/">Travel Agency-03</a></li>
                            <li class=""><a href="../travel-agency-04/">Travel Agency-04</a></li>
                            <li class="active"><a href="">Experience-01</a></li>
                            <li class=""><a href="../experience-02/">Experience-02</a></li>
                            <li class=""><a href="../visa-agency/">Visa Agency</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children position-inherit"><a class="drop-down"
                            href="../destination/">Destination<i class="bi bi-caret-down-fill"></i></a><i
                            class="bi bi-plus dropdown-icon"></i>
                        <div class="mega-menu none">
                            <div class="container">
                                <div class="menu-row">
                                    <div class="menu-single-item">
                                        <div class="menu-title">
                                            <h5>Europe</h5>
                                        </div><i class="bi bi-plus dropdown-icon"></i>
                                        <ul class="none">
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Ffrance-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Ffrance-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Ffrance-flag.png%26w%3D48%26q%3D75">Paris,
                                                    France</a></li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuk-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuk-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuk-flag.png%26w%3D48%26q%3D75">United
                                                    Kingdom</a></li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherland-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherland-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherland-flag.png%26w%3D48%26q%3D75">Netherlands</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fitaly-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fitaly-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fitaly-flag.png%26w%3D48%26q%3D75">Italy</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fgreece-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fgreece-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fgreece-flag.png%26w%3D48%26q%3D75">Greece</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fromania-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fromania-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fromania-flag.png%26w%3D48%26q%3D75">Romania</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="menu-single-item">
                                        <div class="menu-title">
                                            <h5>Asia</h5>
                                        </div><i class="bi bi-plus dropdown-icon"></i>
                                        <ul class="none">
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag2.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag2.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag2.png%26w%3D48%26q%3D75">Tokyo,
                                                    Japan</a></li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findonesia-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findonesia-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findonesia-flag.png%26w%3D48%26q%3D75">Indonesia</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fthailand-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fthailand-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fthailand-flag.png%26w%3D48%26q%3D75">Thailand</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmalaysia-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmalaysia-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmalaysia-flag.png%26w%3D48%26q%3D75">Malaysia</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fvietnam-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fvietnam-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fvietnam-flag.png%26w%3D48%26q%3D75">Hanoi,
                                                    Vietnam</a></li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findia-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findia-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findia-flag.png%26w%3D48%26q%3D75">India</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="menu-single-item">
                                        <div class="menu-title">
                                            <h5>Africa</h5>
                                        </div><i class="bi bi-plus dropdown-icon"></i>
                                        <ul>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fegypt-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fegypt-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fegypt-flag.png%26w%3D48%26q%3D75">Egypt</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D48%26q%3D75">South
                                                    Africa</a></li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fzimbabwe-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fzimbabwe-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fzimbabwe-flag.png%26w%3D48%26q%3D75">Zimbabwe</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkenya-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkenya-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkenya-flag.png%26w%3D48%26q%3D75">Kenya</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmorocco-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmorocco-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmorocco-flag.png%26w%3D48%26q%3D75">Morocco</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsenegal-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsenegal-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsenegal-flag.png%26w%3D48%26q%3D75">Senegal</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="menu-single-item">
                                        <div class="menu-title">
                                            <h5>Oceania</h5>
                                        </div><i class="bi bi-plus dropdown-icon"></i>
                                        <ul class="none">
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Faustralia-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Faustralia-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Faustralia-flag.png%26w%3D48%26q%3D75">Australia</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D48%26q%3D75">New
                                                    Zealand</a></li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpapua-new-guinea-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpapua-new-guinea-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpapua-new-guinea-flag.png%26w%3D48%26q%3D75">Papua
                                                    New Guinea</a></li>
                                        </ul>
                                    </div>
                                    <div class="menu-single-item">
                                        <div class="menu-title">
                                            <h5>Middle East</h5>
                                        </div><i class="bi bi-plus dropdown-icon"></i>
                                        <ul class="none">
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuae-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuae-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuae-flag.png%26w%3D48%26q%3D75">United
                                                    Arab Emirates</a></li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fqatar-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fqatar-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fqatar-flag.png%26w%3D48%26q%3D75">Qatar</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fbahrain-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fbahrain-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fbahrain-flag.png%26w%3D48%26q%3D75">Bahrain</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsaudi-arabia-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsaudi-arabia-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsaudi-arabia-flag.png%26w%3D48%26q%3D75">Saudi
                                                    Arabia</a></li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjordan-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjordan-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjordan-flag.png%26w%3D48%26q%3D75">Jordan</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpalestine-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpalestine-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpalestine-flag.png%26w%3D48%26q%3D75">Palestine</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="menu-single-item">
                                        <div class="menu-title">
                                            <h5>North America</h5>
                                        </div><i class="bi bi-plus dropdown-icon"></i>
                                        <ul class="none">
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fus-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fus-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fus-flag.png%26w%3D48%26q%3D75">United
                                                    States</a></li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcanada-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcanada-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcanada-flag.png%26w%3D48%26q%3D75">Canada</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmexico-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmexico-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmexico-flag.png%26w%3D48%26q%3D75">Mexico</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjamaica-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjamaica-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjamaica-flag.png%26w%3D48%26q%3D75">Jamaica</a>
                                            </li>
                                            <li><a href="../destination/details/"><img alt="" loading="lazy" width="18"
                                                        height="18" decoding="async" data-nimg="1"
                                                        style="color:transparent"
                                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcosta-rica-flag.png%26w%3D32%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcosta-rica-flag.png%26w%3D48%26q%3D75 2x"
                                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcosta-rica-flag.png%26w%3D48%26q%3D75">Costa
                                                    Rica</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div><img alt="" loading="lazy" width="275" height="365" decoding="async" data-nimg="1"
                                class="vector1" style="color:transparent"
                                src="../assets/img/home1/mega-menu-vector1.svg"><img alt="" loading="lazy" width="275"
                                height="365" decoding="async" data-nimg="1" class="vector2" style="color:transparent"
                                src="../assets/img/home1/mega-menu-vector2.svg">
                        </div>
                    </li>
                    <li class="menu-item-has-children "><a class="drop-down" href="../travel-package/">Travel Package<i
                                class="bi bi-caret-down-fill"></i></a><i class="bi bi-plus dropdown-icon"></i>
                        <ul class="sub-menu none">
                            <li class=""><a href="../travel-package/">Travel Package Style 01</a></li>
                            <li class=""><a href="../travel-package/style2/">Travel Package Style 02</a></li>
                            <li class=""><a href="../travel-package/details/">Travel Package Details</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children "><a class="drop-down" href="../visa/">Visa<i
                                class="bi bi-caret-down-fill"></i></a><i class="bi bi-plus dropdown-icon"></i>
                        <ul class="sub-menu none">
                            <li class=""><a href="../visa/">Visa Package</a></li>
                            <li class=""><a href="../visa/details/">Visa Package Details</a></li>
                        </ul>
                    </li>
                    <li class="menu-item-has-children "><a href="#" class="drop-down">Pages<i
                                class="bi bi-caret-down-fill"></i></a><i class="bi bi-plus dropdown-icon"></i>
                        <ul class="sub-menu none">
                            <li class=""><a href="../about/">About GoFly</a></li>
                            <li><a href="../destination/">Destination</a><i
                                    class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                    class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                                <ul class="sub-menu none">
                                    <li class=""><a href="../destination/">Destination Style 01</a></li>
                                    <li class=""><a href="../destination/style2/">Destination Style 02</a></li>
                                    <li class=""><a href="../destination/style3/">Destination Style 03</a></li>
                                    <li class=""><a href="../destination/style4/">Destination Style 04</a></li>
                                    <li class=""><a href="../destination/style5/">Destination Style 05</a></li>
                                    <li class=""><a href="../destination/style6/">Destination Style 06</a></li>
                                    <li class=""><a href="../destination/details/">Destination Details</a></li>
                                </ul>
                            </li>
                            <li><a href="../experience-grid/">Experience</a><i
                                    class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                    class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                                <ul class="sub-menu none">
                                    <li class=""><a href="../experience-grid/">Experience Grid</a></li>
                                    <li class=""><a href="../experience-details/">Experience Details</a></li>
                                </ul>
                            </li>
                            <li><a href="../hotel/">Hotel</a><i
                                    class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                    class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                                <ul class="sub-menu none">
                                    <li class=""><a href="../hotel/">Hotel</a></li>
                                    <li class=""><a href="../hotel/details/">Hotel Details</a></li>
                                </ul>
                            </li>
                            <li><a href="../travel-inspiration/">Travel Inspiration</a><i
                                    class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                    class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                                <ul class="sub-menu none">
                                    <li class=""><a href="../travel-inspiration/">Travel Inspiration Style 01</a></li>
                                    <li class=""><a href="../travel-inspiration/style2/">Travel Inspiration Style 02</a>
                                    </li>
                                    <li class=""><a href="../travel-inspiration/style3/">Travel Inspiration Style 03</a>
                                    </li>
                                    <li class=""><a href="../travel-inspiration/details/">Travel Inspiration Details</a>
                                    </li>
                                </ul>
                            </li>
                            <li><a href="../guider/">Guider</a><i
                                    class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                    class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                                <ul class="sub-menu none">
                                    <li class=""><a href="../guider/">Guider</a></li>
                                    <li class=""><a href="../guider-details/">Guider Details</a></li>
                                </ul>
                            </li>
                            <li><a href="../shop/">Shop</a><i
                                    class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                    class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                                <ul class="sub-menu none">
                                    <li class=""><a href="../shop/">Shop</a></li>
                                    <li class=""><a href="../cart/">Cart</a></li>
                                    <li class=""><a href="../checkout/">Checkout</a></li>
                                    <li class=""><a href="../product-details/">Product Details</a></li>
                                </ul>
                            </li>
                            <li class=""><a href="../faq/">Faq</a></li>
                            <li class=""><a href="../error/">404</a></li>
                        </ul>
                    </li>
                    <li class=""><a href="../contact/">Contact</a></li>
                </ul>
                <div class="language-and-login-area d-lg-none d-block">
                    <div class="language-area">
                        <div class="language-btn">
                            <div class="icon-and-content"><svg width="14" height="14" viewBox="0 0 14 14"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <path
                                            d="M7 14C5.13023 14 3.37239 13.2719 2.05023 11.9498C0.728137 10.6276 0 8.86977 0 7C0 5.13023 0.728137 3.37239 2.05023 2.05023C3.37239 0.728137 5.13023 0 7 0C8.86977 0 10.6276 0.728137 11.9498 2.05023C13.2719 3.37239 14 5.13023 14 7C14 8.86977 13.2719 10.6276 11.9498 11.9498C10.6276 13.2719 8.86977 14 7 14ZM7 0.583324C3.46183 0.583324 0.583324 3.46183 0.583324 7C0.583324 10.5382 3.46183 13.4166 7 13.4166C10.5382 13.4166 13.4166 10.5382 13.4166 7C13.4166 3.46183 10.5382 0.583324 7 0.583324Z">
                                        </path>
                                        <path
                                            d="M7 14C5.90297 14 4.8854 13.2486 4.13468 11.8841C3.41431 10.5747 3.01758 8.84018 3.01758 7C3.01758 5.15982 3.41431 3.42527 4.13468 2.11589C4.8854 0.751433 5.90297 0 7 0C8.09704 0 9.11461 0.751433 9.8653 2.11589C10.5857 3.42527 10.9824 5.15982 10.9824 7C10.9824 8.84018 10.5857 10.5747 9.8653 11.8841C9.11461 13.2486 8.09704 14 7 14ZM7 0.583324C6.12536 0.583324 5.2893 1.22746 4.64579 2.39709C3.97198 3.62179 3.6009 5.25645 3.6009 7C3.6009 8.74355 3.97198 10.3782 4.64576 11.6029C5.28927 12.7725 6.12533 13.4166 6.99998 13.4166C7.87462 13.4166 8.71068 12.7725 9.35419 11.6029C10.028 10.3782 10.3991 8.74355 10.3991 7C10.3991 5.25645 10.028 3.62179 9.35419 2.39709C8.71071 1.22746 7.87462 0.583324 7 0.583324Z">
                                        </path>
                                        <path
                                            d="M6.99968 13.9573C6.8386 13.9573 6.70801 13.8267 6.70801 13.6657V0.334156C6.70801 0.173074 6.83857 0.0424805 6.99968 0.0424805C7.16077 0.0424805 7.29136 0.173074 7.29136 0.334156V13.6657C7.29136 13.8267 7.16077 13.9573 6.99968 13.9573Z">
                                        </path>
                                        <path
                                            d="M13.6661 7.29147H0.334644C0.173562 7.29147 0.0429688 7.16088 0.0429688 6.99979C0.0429688 6.83871 0.173562 6.70812 0.334644 6.70812H13.6661C13.8272 6.70812 13.9578 6.83868 13.9578 6.99979C13.9578 7.16088 13.8272 7.29147 13.6661 7.29147ZM12.7022 3.81187H1.29862C1.13754 3.81187 1.00695 3.6813 1.00695 3.52019C1.00695 3.35908 1.13751 3.22852 1.29862 3.22852H12.7022C12.8633 3.22852 12.9939 3.35908 12.9939 3.52019C12.9939 3.6813 12.8632 3.81187 12.7022 3.81187ZM12.7022 10.771H1.29862C1.13754 10.771 1.00695 10.6404 1.00695 10.4794C1.00695 10.3183 1.13751 10.1877 1.29862 10.1877H12.7022C12.8633 10.1877 12.9939 10.3183 12.9939 10.4794C12.9939 10.6404 12.8632 10.771 12.7022 10.771Z">
                                        </path>
                                    </g>
                                </svg><span>EN</span></div><i class="bi bi-caret-down-fill"></i>
                        </div>
                        <ul class="language-list ">
                            <li><a href="#"><img alt="" loading="lazy" width="550" height="220" decoding="async"
                                        data-nimg="1" style="color:transparent"
                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fengland-flag.png%26w%3D640%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fengland-flag.png%26w%3D1200%26q%3D75 2x"
                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fengland-flag.png%26w%3D1200%26q%3D75">English</a>
                            </li>
                            <li><a href="#"><img alt="" loading="lazy" width="550" height="220" decoding="async"
                                        data-nimg="1" style="color:transparent"
                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherlands-flag.png%26w%3D640%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherlands-flag.png%26w%3D1200%26q%3D75 2x"
                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherlands-flag.png%26w%3D1200%26q%3D75">Dutch</a>
                            </li>
                            <li><a href="#"><img alt="" loading="lazy" width="550" height="220" decoding="async"
                                        data-nimg="1" style="color:transparent"
                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag.png%26w%3D640%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag.png%26w%3D1200%26q%3D75 2x"
                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag.png%26w%3D1200%26q%3D75">Japanese</a>
                            </li>
                            <li><a href="#"><img alt="" loading="lazy" width="550" height="220" decoding="async"
                                        data-nimg="1" style="color:transparent"
                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkorea-flag.png%26w%3D640%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkorea-flag.png%26w%3D1200%26q%3D75 2x"
                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkorea-flag.png%26w%3D1200%26q%3D75">Korean</a>
                            </li>
                            <li><a href="#"><img alt="" loading="lazy" width="550" height="220" decoding="async"
                                        data-nimg="1" style="color:transparent"
                                        srcset="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fchina-flag.png%26w%3D640%26q%3D75 1x, ../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fchina-flag.png%26w%3D1200%26q%3D75 2x"
                                        src="../_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fchina-flag.png%26w%3D1200%26q%3D75">Chinese</a>
                            </li>
                        </ul>
                    </div><a href="#" class="primary-btn1 three black-bg"><span><svg width="15" height="15"
                                viewBox="0 0 15 15" xmlns="http://www.w3.org/2000/svg">
                                <g>
                                    <path
                                        d="M7.50105 7.78913C9.64392 7.78913 11.3956 6.03744 11.3956 3.89456C11.3956 1.75169 9.64392 0 7.50105 0C5.35818 0 3.60652 1.75169 3.60652 3.89456C3.60652 6.03744 5.35821 7.78913 7.50105 7.78913ZM14.1847 10.9014C14.0827 10.6463 13.9467 10.4082 13.7936 10.1871C13.0113 9.0306 11.8038 8.2653 10.4433 8.07822C10.2732 8.06123 10.0861 8.09522 9.95007 8.19727C9.23578 8.72448 8.38546 8.99658 7.50108 8.99658C6.61671 8.99658 5.76638 8.72448 5.05209 8.19727C4.91603 8.09522 4.72895 8.04421 4.5589 8.07822C3.19835 8.2653 1.97387 9.0306 1.20857 10.1871C1.05551 10.4082 0.919443 10.6633 0.817424 10.9014C0.766415 11.0034 0.783407 11.1225 0.834416 11.2245C0.970484 11.4626 1.14054 11.7007 1.2936 11.9048C1.53168 12.2279 1.78679 12.517 2.07592 12.7891C2.31401 13.0272 2.58611 13.2483 2.85824 13.4694C4.20177 14.4728 5.81742 15 7.48409 15C9.15076 15 10.7664 14.4728 12.1099 13.4694C12.382 13.2653 12.6541 13.0272 12.8923 12.7891C13.1644 12.517 13.4365 12.2279 13.6746 11.9048C13.8446 11.6837 13.9977 11.4626 14.1338 11.2245C14.2188 11.1225 14.2358 11.0034 14.1847 10.9014Z">
                                    </path>
                                </g>
                            </svg>Login</span><span><svg width="15" height="15" viewBox="0 0 15 15"
                                xmlns="http://www.w3.org/2000/svg">
                                <g>
                                    <path
                                        d="M7.50105 7.78913C9.64392 7.78913 11.3956 6.03744 11.3956 3.89456C11.3956 1.75169 9.64392 0 7.50105 0C5.35818 0 3.60652 1.75169 3.60652 3.89456C3.60652 6.03744 5.35821 7.78913 7.50105 7.78913ZM14.1847 10.9014C14.0827 10.6463 13.9467 10.4082 13.7936 10.1871C13.0113 9.0306 11.8038 8.2653 10.4433 8.07822C10.2732 8.06123 10.0861 8.09522 9.95007 8.19727C9.23578 8.72448 8.38546 8.99658 7.50108 8.99658C6.61671 8.99658 5.76638 8.72448 5.05209 8.19727C4.91603 8.09522 4.72895 8.04421 4.5589 8.07822C3.19835 8.2653 1.97387 9.0306 1.20857 10.1871C1.05551 10.4082 0.919443 10.6633 0.817424 10.9014C0.766415 11.0034 0.783407 11.1225 0.834416 11.2245C0.970484 11.4626 1.14054 11.7007 1.2936 11.9048C1.53168 12.2279 1.78679 12.517 2.07592 12.7891C2.31401 13.0272 2.58611 13.2483 2.85824 13.4694C4.20177 14.4728 5.81742 15 7.48409 15C9.15076 15 10.7664 14.4728 12.1099 13.4694C12.382 13.2653 12.6541 13.0272 12.8923 12.7891C13.1644 12.517 13.4365 12.2279 13.6746 11.9048C13.8446 11.6837 13.9977 11.4626 14.1338 11.2245C14.2188 11.1225 14.2358 11.0034 14.1847 10.9014Z">
                                    </path>
                                </g>
                            </svg>Login</span></a>
                </div>
            </div>
            <div class="nav-right">
                <div class="search-bar d-lg-none d-block">
                    <div class="search-btn"><svg width="16" height="16" viewBox="0 0 16 16"
                            xmlns="http://www.w3.org/2000/svg">
                            <g>
                                <path
                                    d="M15.7417 14.6098L13.486 12.3621C14.7088 10.8514 15.3054 8.9291 15.1526 6.99153C14.9998 5.05396 14.1093 3.24888 12.6648 1.94851C11.2203 0.648146 9.33193 -0.0483622 7.38901 0.00261294C5.44609 0.0535881 3.59681 0.84816 2.22248 2.22248C0.84816 3.59681 0.0535881 5.44609 0.00261294 7.38901C-0.0483622 9.33193 0.648146 11.2203 1.94851 12.6648C3.24888 14.1093 5.05396 14.9998 6.99153 15.1526C8.9291 15.3054 10.8514 14.7088 12.3621 13.486L14.6098 15.7417C14.6839 15.8164 14.7721 15.8757 14.8692 15.9161C14.9664 15.9566 15.0705 15.9774 15.1758 15.9774C15.281 15.9774 15.3852 15.9566 15.4823 15.9161C15.5794 15.8757 15.6676 15.8164 15.7417 15.7417C15.8164 15.6676 15.8757 15.5794 15.9161 15.4823C15.9566 15.3852 15.9774 15.281 15.9774 15.1758C15.9774 15.0705 15.9566 14.9664 15.9161 14.8692C15.8757 14.7721 15.8164 14.6839 15.7417 14.6098ZM1.62572 7.60368C1.62572 6.42135 1.97632 5.26557 2.63319 4.2825C3.29005 3.29943 4.22368 2.53322 5.31601 2.08076C6.40834 1.62831 7.61031 1.50992 8.76992 1.74058C9.92953 1.97124 10.9947 2.54059 11.8307 3.37662C12.6668 4.21266 13.2361 5.27783 13.4668 6.43744C13.6974 7.59705 13.579 8.79902 13.1266 9.89134C12.6741 10.9837 11.9079 11.9173 10.9249 12.5742C9.94178 13.231 8.78601 13.5816 7.60368 13.5816C6.01822 13.5816 4.49771 12.9518 3.37662 11.8307C2.25554 10.7096 1.62572 9.18913 1.62572 7.60368Z">
                                </path>
                            </g>
                        </svg></div>
                    <div class="search-input ">
                        <div class="search-close"></div>
                        <form>
                            <div class="search-group">
                                <div class="form-inner2"><input type="text" placeholder="Enter your keywords"><button
                                        type="submit"><i class="bi bi-search"></i></button></div>
                            </div>
                            <div class="quick-search">
                                <ul>
                                    <li>Quick Search :</li>
                                    <li><a href="../travel-package/">Thailand Tour,</a></li>
                                    <li><a href="../travel-package/">Philippines Tour,</a></li>
                                    <li><a href="../travel-package/">Bali Tour,</a></li>
                                    <li><a href="../travel-package/">Hawaii, USA Tour,</a></li>
                                    <li><a href="../travel-package/">Switzerland Tour,</a></li>
                                    <li><a href="../travel-package/">Maldives Tour,</a></li>
                                    <li><a href="../travel-package/">Paris Tour,</a></li>
                                </ul>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="sidebar-button mobile-menu-btn "><svg width="20" height="18" viewBox="0 0 20 18"
                        xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M1.29445 2.8421H10.5237C11.2389 2.8421 11.8182 2.2062 11.8182 1.42105C11.8182 0.635903 11.2389 0 10.5237 0H1.29445C0.579249 0 0 0.635903 0 1.42105C0 2.2062 0.579249 2.8421 1.29445 2.8421Z">
                        </path>
                        <path
                            d="M1.23002 10.421H18.77C19.4496 10.421 20 9.78506 20 8.99991C20 8.21476 19.4496 7.57886 18.77 7.57886H1.23002C0.550421 7.57886 0 8.21476 0 8.99991C0 9.78506 0.550421 10.421 1.23002 10.421Z">
                        </path>
                        <path
                            d="M18.8052 15.1579H10.2858C9.62563 15.1579 9.09094 15.7938 9.09094 16.5789C9.09094 17.3641 9.62563 18 10.2858 18H18.8052C19.4653 18 20 17.3641 20 16.5789C20 15.7938 19.4653 15.1579 18.8052 15.1579Z">
                        </path>
                    </svg></div>
            </div>
        </div>
    </header>