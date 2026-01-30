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


<div class="topbar-area d-lg-block d-none">
    <div class="container">
        <div class="topbar-wrap">
            <div class="logo-and-search-area"><a class="header-logo" href="<?= site_url() ?>"><img alt="" loading="lazy"
                        width="200" height="200" decoding="async" data-nimg="1" style="color:transparent"
                        src="<?= base_url('assets/images/logo.svg') ?>"></a>
                <form class="search-area">
                    <div class="form-inner"><button type="submit"><svg width="16" height="16" viewBox="0 0 16 16"
                                xmlns="http://www.w3.org/2000/svg">
                                <g>
                                    <path
                                        d="M15.8044 14.8855L13.0544 12.198L12.99 12.1002C12.8688 11.9807 12.7055 11.9137 12.5353 11.9137C12.3651 11.9137 12.2018 11.9807 12.0806 12.1002C9.74343 14.2443 6.14312 14.3605 3.66561 12.3724C1.18811 10.3843 0.604677 6.90645 2.30061 4.24832C3.99655 1.5902 7.44655 0.573637 10.3631 1.87332C13.2797 3.17301 14.755 6.38739 13.8125 9.38239C13.7793 9.48905 13.7753 9.60268 13.8011 9.71137C13.8269 9.82007 13.8815 9.91983 13.9591 10.0002C14.0375 10.082 14.1358 10.1421 14.2443 10.1746C14.3528 10.2071 14.4679 10.211 14.5784 10.1858C14.6883 10.1616 14.79 10.109 14.8732 10.0332C14.9564 9.95744 15.0182 9.86113 15.0525 9.75395C16.1775 6.19989 14.4781 2.37489 11.0525 0.75395C7.62686 -0.866988 3.50468 0.200824 1.35124 3.26864C-0.802198 6.33645 -0.34001 10.4818 2.43905 13.0239C5.21811 15.5661 9.47968 15.7408 12.4687 13.4377L14.9037 15.8183C15.026 15.9358 15.1889 16.0014 15.3584 16.0014C15.5279 16.0014 15.6909 15.9358 15.8131 15.8183C15.8728 15.7599 15.9201 15.6902 15.9525 15.6133C15.9848 15.5363 16.0015 15.4537 16.0015 15.3702C16.0015 15.2867 15.9848 15.2041 15.9525 15.1271C15.9201 15.0502 15.8728 14.9805 15.8131 14.9221L15.8044 14.8855Z">
                                    </path>
                                </g>
                            </svg></button><input type="text" placeholder="Find Your Perfect Tour Package"></div>
                </form>
            </div>
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
                                        src="<?= base_url('assets/images/home/en-us') ?>">English</a>
                            </li>
                            <li><a href="#"><img alt="" loading="lazy" width="18" height="18" decoding="async"
                                        data-nimg="1" style="color:transparent"
                                        srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherlands-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherlands-flag.png%26w%3D48%26q%3D75 2x"
                                        src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherlands-flag.png%26w%3D48%26q%3D75">Việt Nam</a>
                            </li>
                        </ul>
                    </div>
                </div><a href="#" class="primary-btn1 black-bg"><span><svg width="15" height="15" viewBox="0 0 15 15"
                            xmlns="http://www.w3.org/2000/svg">
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
<header class="style-1">
    <div class="container d-flex flex-nowrap align-items-center justify-content-between"><a
            class="header-logo d-lg-none d-block" href=""><img alt="" loading="lazy" width="100" height="40"
                decoding="async" data-nimg="1" style="color:transparent" src="assets/img/header-logo.svg"></a>
        <div class="main-menu ">
            <div class="mobile-logo-area d-lg-none d-flex align-items-center justify-content-between"><a
                    class="mobile-logo-wrap" href=""><img alt="" loading="lazy" width="100" height="40" decoding="async"
                        data-nimg="1" style="color:transparent" src="assets/img/header-logo.svg"></a>
                <div class="menu-close-btn"><i class="bi bi-x"></i></div>
            </div>
            <ul class="menu-list">
                <li class="active"><a href="">Home</a></li>
                <li class="menu-item-has-children position-inherit"><a class="drop-down"
                        href="destination/">Destination<i class="bi bi-caret-down-fill"></i></a><i
                        class="bi bi-plus dropdown-icon"></i>
                    <div class="mega-menu none">
                        <div class="container">
                            <div class="menu-row">
                                <div class="menu-single-item">
                                    <div class="menu-title">
                                        <h5>Europe</h5>
                                    </div><i class="bi bi-plus dropdown-icon"></i>
                                    <ul class="none">
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Ffrance-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Ffrance-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Ffrance-flag.png%26w%3D48%26q%3D75">Paris,
                                                France</a></li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuk-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuk-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuk-flag.png%26w%3D48%26q%3D75">United
                                                Kingdom</a></li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherland-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherland-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherland-flag.png%26w%3D48%26q%3D75">Netherlands</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fitaly-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fitaly-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fitaly-flag.png%26w%3D48%26q%3D75">Italy</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fgreece-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fgreece-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fgreece-flag.png%26w%3D48%26q%3D75">Greece</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fromania-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fromania-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fromania-flag.png%26w%3D48%26q%3D75">Romania</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="menu-single-item">
                                    <div class="menu-title">
                                        <h5>Asia</h5>
                                    </div><i class="bi bi-plus dropdown-icon"></i>
                                    <ul class="none">
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag2.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag2.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag2.png%26w%3D48%26q%3D75">Tokyo,
                                                Japan</a></li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findonesia-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findonesia-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findonesia-flag.png%26w%3D48%26q%3D75">Indonesia</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fthailand-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fthailand-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fthailand-flag.png%26w%3D48%26q%3D75">Thailand</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmalaysia-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmalaysia-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmalaysia-flag.png%26w%3D48%26q%3D75">Malaysia</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fvietnam-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fvietnam-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fvietnam-flag.png%26w%3D48%26q%3D75">Hanoi,
                                                Vietnam</a></li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findia-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findia-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Findia-flag.png%26w%3D48%26q%3D75">India</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="menu-single-item">
                                    <div class="menu-title">
                                        <h5>Africa</h5>
                                    </div><i class="bi bi-plus dropdown-icon"></i>
                                    <ul>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fegypt-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fegypt-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fegypt-flag.png%26w%3D48%26q%3D75">Egypt</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D48%26q%3D75">South
                                                Africa</a></li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fzimbabwe-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fzimbabwe-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fzimbabwe-flag.png%26w%3D48%26q%3D75">Zimbabwe</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkenya-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkenya-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkenya-flag.png%26w%3D48%26q%3D75">Kenya</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmorocco-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmorocco-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmorocco-flag.png%26w%3D48%26q%3D75">Morocco</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsenegal-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsenegal-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsenegal-flag.png%26w%3D48%26q%3D75">Senegal</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="menu-single-item">
                                    <div class="menu-title">
                                        <h5>Oceania</h5>
                                    </div><i class="bi bi-plus dropdown-icon"></i>
                                    <ul class="none">
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Faustralia-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Faustralia-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Faustralia-flag.png%26w%3D48%26q%3D75">Australia</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsouth-africa-flag.png%26w%3D48%26q%3D75">New
                                                Zealand</a></li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpapua-new-guinea-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpapua-new-guinea-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpapua-new-guinea-flag.png%26w%3D48%26q%3D75">Papua
                                                New Guinea</a></li>
                                    </ul>
                                </div>
                                <div class="menu-single-item">
                                    <div class="menu-title">
                                        <h5>Middle East</h5>
                                    </div><i class="bi bi-plus dropdown-icon"></i>
                                    <ul class="none">
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuae-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuae-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fuae-flag.png%26w%3D48%26q%3D75">United
                                                Arab Emirates</a></li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fqatar-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fqatar-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fqatar-flag.png%26w%3D48%26q%3D75">Qatar</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fbahrain-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fbahrain-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fbahrain-flag.png%26w%3D48%26q%3D75">Bahrain</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsaudi-arabia-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsaudi-arabia-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fsaudi-arabia-flag.png%26w%3D48%26q%3D75">Saudi
                                                Arabia</a></li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjordan-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjordan-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjordan-flag.png%26w%3D48%26q%3D75">Jordan</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpalestine-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpalestine-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fpalestine-flag.png%26w%3D48%26q%3D75">Palestine</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="menu-single-item">
                                    <div class="menu-title">
                                        <h5>North America</h5>
                                    </div><i class="bi bi-plus dropdown-icon"></i>
                                    <ul class="none">
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fus-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fus-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fus-flag.png%26w%3D48%26q%3D75">United
                                                States</a></li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcanada-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcanada-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcanada-flag.png%26w%3D48%26q%3D75">Canada</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmexico-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmexico-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fmexico-flag.png%26w%3D48%26q%3D75">Mexico</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjamaica-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjamaica-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjamaica-flag.png%26w%3D48%26q%3D75">Jamaica</a>
                                        </li>
                                        <li><a href="destination/details/"><img alt="" loading="lazy" width="18"
                                                    height="18" decoding="async" data-nimg="1" style="color:transparent"
                                                    srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcosta-rica-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcosta-rica-flag.png%26w%3D48%26q%3D75 2x"
                                                    src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fcosta-rica-flag.png%26w%3D48%26q%3D75">Costa
                                                Rica</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div><img alt="" loading="lazy" width="275" height="365" decoding="async" data-nimg="1"
                            class="vector1" style="color:transparent" src="assets/img/home1/mega-menu-vector1.svg"><img
                            alt="" loading="lazy" width="275" height="365" decoding="async" data-nimg="1"
                            class="vector2" style="color:transparent" src="assets/img/home1/mega-menu-vector2.svg">
                    </div>
                </li>
                <li class="menu-item-has-children "><a class="drop-down" href="travel-package/">Travel Package<i
                            class="bi bi-caret-down-fill"></i></a><i class="bi bi-plus dropdown-icon"></i>
                    <ul class="sub-menu none">
                        <li class=""><a href="travel-package/">Travel Package Style 01</a></li>
                        <li class=""><a href="travel-package/style2/">Travel Package Style 02</a></li>
                        <li class=""><a href="travel-package/details/">Travel Package Details</a></li>
                    </ul>
                </li>
                <li class="menu-item-has-children "><a class="drop-down" href="visa/">Visa<i
                            class="bi bi-caret-down-fill"></i></a><i class="bi bi-plus dropdown-icon"></i>
                    <ul class="sub-menu none">
                        <li class=""><a href="visa/">Visa Package</a></li>
                        <li class=""><a href="visa/details/">Visa Package Details</a></li>
                    </ul>
                </li>
                <li class="menu-item-has-children "><a href="#" class="drop-down">Pages<i
                            class="bi bi-caret-down-fill"></i></a><i class="bi bi-plus dropdown-icon"></i>
                    <ul class="sub-menu none">
                        <li class=""><a href="about/">About GoFly</a></li>
                        <li><a href="destination/">Destination</a><i
                                class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                            <ul class="sub-menu none">
                                <li class=""><a href="destination/">Destination Style 01</a></li>
                                <li class=""><a href="destination/style2/">Destination Style 02</a></li>
                                <li class=""><a href="destination/style3/">Destination Style 03</a></li>
                                <li class=""><a href="destination/style4/">Destination Style 04</a></li>
                                <li class=""><a href="destination/style5/">Destination Style 05</a></li>
                                <li class=""><a href="destination/style6/">Destination Style 06</a></li>
                                <li class=""><a href="destination/details/">Destination Details</a></li>
                            </ul>
                        </li>
                        <li><a href="experience-grid/">Experience</a><i
                                class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                            <ul class="sub-menu none">
                                <li class=""><a href="experience-grid/">Experience Grid</a></li>
                                <li class=""><a href="experience-details/">Experience Details</a></li>
                            </ul>
                        </li>
                        <li><a href="hotel/">Hotel</a><i
                                class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                            <ul class="sub-menu none">
                                <li class=""><a href="hotel/">Hotel</a></li>
                                <li class=""><a href="hotel/details/">Hotel Details</a></li>
                            </ul>
                        </li>
                        <li><a href="travel-inspiration/">Travel Inspiration</a><i
                                class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                            <ul class="sub-menu none">
                                <li class=""><a href="travel-inspiration/">Travel Inspiration Style 01</a></li>
                                <li class=""><a href="travel-inspiration/style2/">Travel Inspiration Style 02</a>
                                </li>
                                <li class=""><a href="travel-inspiration/style3/">Travel Inspiration Style 03</a>
                                </li>
                                <li class=""><a href="travel-inspiration/details/">Travel Inspiration Details</a>
                                </li>
                            </ul>
                        </li>
                        <li><a href="guider/">Guider</a><i
                                class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                            <ul class="sub-menu none">
                                <li class=""><a href="guider/">Guider</a></li>
                                <li class=""><a href="guider-details/">Guider Details</a></li>
                            </ul>
                        </li>
                        <li><a href="shop/">Shop</a><i class="d-lg-flex d-none bi-caret-right-fill dropdown-icon"></i><i
                                class="d-lg-none d-flex bi bi-plus dropdown-icon"></i>
                            <ul class="sub-menu none">
                                <li class=""><a href="shop/">Shop</a></li>
                                <li class=""><a href="cart/">Cart</a></li>
                                <li class=""><a href="checkout/">Checkout</a></li>
                                <li class=""><a href="product-details/">Product Details</a></li>
                            </ul>
                        </li>
                        <li class=""><a href="faq/">Faq</a></li>
                        <li class=""><a href="error/">404</a></li>
                    </ul>
                </li>
                <li class=""><a href="contact/">Contact</a></li>
            </ul>
            <div class="contact-area d-lg-none d-flex">
                <div class="single-contact">
                    <div class="icon"><img alt="" loading="lazy" width="20" height="20" decoding="async" data-nimg="1"
                            style="color:transparent" src="<?= base_url('assets/images/home/hotline.svg') ?>">
                    </div>
                    <div class="content"><span>Hotline</span><a href="https://wa.me/91345533865">+84 79 568 1 568</a></div>
                </div><i class="bi bi-caret-down-fill contact-dropdown-btn"></i>
                <ul class="contact-list ">
                    <li class="single-contact">
                        <div class="icon"><img alt="" loading="lazy" width="20" height="20" decoding="async"
                                data-nimg="1" style="color:transparent" src="assets/img/home1/icon/mail-icon.svg">
                        </div>
                        <div class="content"><span>Mail Support</span><a
                                href="mailto:info@example.com">info@example.com</a></div>
                    </li>
                    <li class="single-contact">
                        <div class="icon"><img alt="" loading="lazy" width="20" height="20" decoding="async"
                                data-nimg="1" style="color:transparent" src="assets/img/home1/icon/live-chat.svg">
                        </div>
                        <div class="content"><span>More Inquery</span><a href="https://wa.me/91345533865">+91 345
                                533 865</a></div>
                    </li>
                </ul>
            </div><a href="#" class="primary-btn1 black-bg d-lg-none d-flex"><span><svg width="15" height="15"
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
        <div class="nav-right">
            <div class="contact-area d-lg-flex d-none">
                <div class="single-contact">
                    <div class="icon"><img alt="" loading="lazy" width="20" height="20" decoding="async" data-nimg="1"
                            style="color:transparent" src="<?= base_url('assets/images/home/hotline.svg') ?>">
                    </div>
                    <div class="content"><span>Hotline</span><a href="https://wa.me/91345533865">+84 79 568 1 568</a></div>
                </div><i class="bi bi-caret-down-fill contact-dropdown-btn"></i>
                <ul class="contact-list ">
                    <li class="single-contact">
                        <div class="icon"><img alt="" loading="lazy" width="20" height="20" decoding="async"
                                data-nimg="1" style="color:transparent" src="assets/img/home1/icon/mail-icon.svg">
                        </div>
                        <div class="content"><span>Mail Support</span><a
                                href="mailto:info@example.com">info@example.com</a></div>
                    </li>
                    <li class="single-contact">
                        <div class="icon"><img alt="" loading="lazy" width="20" height="20" decoding="async"
                                data-nimg="1" style="color:transparent" src="assets/img/home1/icon/live-chat.svg">
                        </div>
                        <div class="content"><span>More Inquery</span><a href="https://wa.me/91345533865">+91 345
                                533 865</a></div>
                    </li>
                </ul>
            </div>
            <div class="language-area d-lg-none d-block">
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
                    <li><a href="#"><img alt="" loading="lazy" width="18" height="18" decoding="async" data-nimg="1"
                                style="color:transparent"
                                srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fengland-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fengland-flag.png%26w%3D48%26q%3D75 2x"
                                src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fengland-flag.png%26w%3D48%26q%3D75">English</a>
                    </li>
                    <li><a href="#"><img alt="" loading="lazy" width="18" height="18" decoding="async" data-nimg="1"
                                style="color:transparent"
                                srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherlands-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherlands-flag.png%26w%3D48%26q%3D75 2x"
                                src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fnetherlands-flag.png%26w%3D48%26q%3D75">Dutch</a>
                    </li>
                    <li><a href="#"><img alt="" loading="lazy" width="18" height="18" decoding="async" data-nimg="1"
                                style="color:transparent"
                                srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag.png%26w%3D48%26q%3D75 2x"
                                src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fjapan-flag.png%26w%3D48%26q%3D75">Japanese</a>
                    </li>
                    <li><a href="#"><img alt="" loading="lazy" width="18" height="18" decoding="async" data-nimg="1"
                                style="color:transparent"
                                srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkorea-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkorea-flag.png%26w%3D48%26q%3D75 2x"
                                src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fkorea-flag.png%26w%3D48%26q%3D75">Korean</a>
                    </li>
                    <li><a href="#"><img alt="" loading="lazy" width="18" height="18" decoding="async" data-nimg="1"
                                style="color:transparent"
                                srcset="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fchina-flag.png%26w%3D32%26q%3D75 1x, _next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fchina-flag.png%26w%3D48%26q%3D75 2x"
                                src="_next/image/url%3D%252Fassets%252Fimg%252Fhome1%252Fchina-flag.png%26w%3D48%26q%3D75">Chinese</a>
                    </li>
                </ul>
            </div>
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
                            <div class="form-inner2"><input type="text"
                                    placeholder="Find Your Perfect Tour Package"><button type="submit"><i
                                        class="bi bi-search"></i></button></div>
                        </div>
                        <div class="quick-search">
                            <ul>
                                <li>Quick Search :</li>
                                <li><a href="travel-package/">Thailand Tour,</a></li>
                                <li><a href="travel-package/">Philippines Tour,</a></li>
                                <li><a href="travel-package/">Bali Tour,</a></li>
                                <li><a href="travel-package/">Hawaii, USA Tour,</a></li>
                                <li><a href="travel-package/">Switzerland Tour,</a></li>
                                <li><a href="travel-package/">Maldives Tour,</a></li>
                                <li><a href="travel-package/">Paris Tour,</a></li>
                            </ul>
                        </div>
                    </form>
                </div>
            </div>
            <div class="sidebar-button mobile-menu-btn"><svg width="20" height="18" viewBox="0 0 20 18"
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