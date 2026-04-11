<div class="bg-dark-custom" style="min-height: 100vh;">
    {{-- Navigation --}}
    <nav class="navbar sticky-top glass-effect border-bottom py-3" style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(10px);">
        <div class="container-fluid">
            <a href="{{ route('home') }}" class="navbar-brand d-flex align-items-center gap-2">
                <div class="icon-container icon-container-sm gradient-bg-primary">
                    <x-lucide-wallet class="text-white w-5 h-5" />
                </div>
                <span class="gradient-text fs-4 fw-bold">CarePay</span>
            </a>
            <div class="ms-auto gap-3 d-flex">
                <a href="{{ route('login') }}" class="btn btn-link text-white text-decoration-none">
                    Login
                </a>
                <a href="{{ route('register') }}" class="btn btn-gradient">
                    Get Started
                </a>
            </div>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section class="py-4 py-md-5" style="padding-top: clamp(3rem, 10vw, 6rem); padding-bottom: clamp(2rem, 10vw, 5rem);">
        <div class="container-fluid">
            <div class="row align-items-center g-3 g-md-5">
                <div class="col-lg-6">
                    <div>
                        <div class="d-inline-flex align-items-center gap-2 px-4 py-2 mb-4 rounded-pill" style="background: rgba(168, 85, 247, 0.1); border: 1px solid rgba(168, 85, 247, 0.2);">
                            <x-lucide-sparkles class="w-4 h-4 text-primary-custom" />
                            <span class="small text-primary-custom">Your Digital Banking Partner</span>
                        </div>
                        <h1 class="display-3 fw-bold mb-4">
                            Banking Made
                            <span class="gradient-text">Simple & Secure</span>
                        </h1>
                        <p class="fs-5 text-muted-custom mb-4 lh-lg">
                            Send money, pay bills, and manage your finances with ease. 
                            Join thousands of users experiencing the future of digital banking.
                        </p>
                        <div class="d-flex flex-column flex-sm-row gap-3">
                            <a href="{{ route('register') }}" class="btn btn-gradient btn-lg px-5 py-3 d-flex align-items-center justify-content-center gap-2 text-decoration-none">
                                Start Free Today
                                <x-lucide-arrow-right class="w-5 h-5" />
                            </a>
                            <a href="#features" class="btn btn-outline-light btn-lg px-5 py-3 text-decoration-none">
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="position-relative">
                        <div class="card card-luxury p-4 shadow-primary-lg position-relative">
                            <div class="blur-circle-primary" style="top: -50px; right: -50px; position: absolute;"></div>
                            <div class="blur-circle-accent" style="bottom: -50px; left: -50px; position: absolute;"></div>
                            
                            <div class="card-body position-relative p-0">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <label class="text-muted-custom small mb-0">Total Balance</label>
                                    <x-lucide-wallet class="w-5 h-5 text-primary-custom" />
                                </div>
                                <div class="display-4 fw-bold mb-4">₦12,450.00</div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="stat-card">
                                            <div class="text-muted-custom small mb-2">Income</div>
                                            <div class="fs-5 fw-semibold text-primary-custom">+₦3,200</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="stat-card">
                                            <div class="text-muted-custom small mb-2">Expenses</div>
                                            <div class="fs-5 fw-semibold text-accent-custom">-₦1,450</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Features Section --}}
    <section class="py-5" id="features">
        <div class="container-fluid">
            <div class="text-center mb-5">
                <h2 class="display-4 fw-bold mb-3">Everything You Need</h2>
                <p class="fs-5 text-muted-custom">
                    Powerful features designed for modern financial management
                </p>
            </div>

            <div class="row g-4">
                @php
                    $features = [
                        ['icon' => 'send', 'title' => 'Send Money', 'description' => 'Transfer funds instantly to anyone, anywhere in the world'],
                        ['icon' => 'receipt', 'title' => 'Pay Bills', 'description' => 'Pay all your bills in one place - electricity, airtime, data and more'],
                        ['icon' => 'wallet', 'title' => 'Digital Wallet', 'description' => 'Manage your money with a secure digital wallet'],
                        ['icon' => 'zap', 'title' => 'Instant Transfers', 'description' => 'Lightning-fast transactions processed in seconds'],
                        ['icon' => 'shield', 'title' => 'Secure & Safe', 'description' => 'Bank-grade security protecting every transaction'],
                        ['icon' => 'check-circle-2', 'title' => 'KYC Verified', 'description' => 'Complete verification process for trusted transactions'],
                    ];
                @endphp

                @foreach($features as $feature)
                    <div class="col-12 col-sm-6 col-md-6 col-lg-4">
                        <div class="card card-luxury p-4 h-100 hover-lift">
                            <div class="card-body p-0">
                                <div class="icon-container icon-container-md mb-3" style="background: rgba(168, 85, 247, 0.2);">
                                    <x-dynamic-component :component="'lucide-' . $feature['icon']" class="w-6 h-6 text-primary-custom" />
                                </div>
                                <h3 class="h5 fw-bold mb-2">{{ $feature['title'] }}</h3>
                                <p class="text-muted-custom mb-0 small">{{ $feature['description'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Security Section --}}
    <section class="py-5">
        <div class="container-fluid">
            <div class="card p-4 p-md-5 shadow-primary" style="background: linear-gradient(135deg, rgba(168, 85, 247, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%); border: 1px solid rgba(168, 85, 247, 0.2);">
                <div class="card-body p-0">
                    <div class="row align-items-center g-3 g-md-5">
                        <div class="col-lg-6">
                            <h2 class="display-4 fw-bold mb-4">Bank-Grade Security</h2>
                            <p class="fs-5 text-muted-custom mb-4 lh-lg">
                                Your money and data are protected with industry-leading security measures.
                            </p>
                            <div class="d-flex flex-column gap-3">
                                @php
                                    $securityFeatures = [
                                        'End-to-end encryption for all transactions',
                                        'Two-factor authentication (2FA)',
                                        'Biometric login support',
                                        'Real-time fraud detection',
                                        'Secure cloud backup'
                                    ];
                                @endphp
                                @foreach($securityFeatures as $item)
                                    <div class="d-flex align-items-center gap-3">
                                        <x-lucide-check-circle-2 class="w-5 h-5 text-primary-custom shrink-0" />
                                        <span class="small">{{ $item }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row g-3">
                                <div class="col-6 col-md-6">
                                    <div class="card card-luxury p-4 text-center h-100">
                                        <div class="card-body p-0">
                                            <x-lucide-shield class="w-10 h-10 text-primary-custom mb-3 mx-auto" />
                                            <div class="h5 fw-bold mb-1">256-bit</div>
                                            <div class="small text-muted-custom">Encryption</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="card card-luxury p-4 text-center h-100">
                                        <div class="card-body p-0">
                                            <x-lucide-lock class="w-10 h-10 text-primary-custom mb-3 mx-auto" />
                                            <div class="h5 fw-bold mb-1">2FA</div>
                                            <div class="small text-muted-custom">Protection</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="card card-luxury p-4 text-center h-100">
                                        <div class="card-body p-0">
                                            <x-lucide-check-circle-2 class="w-10 h-10 text-primary-custom mb-3 mx-auto" />
                                            <div class="h5 fw-bold mb-1">100%</div>
                                            <div class="small text-muted-custom">Verified</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6">
                                    <div class="card card-luxury p-4 text-center h-100">
                                        <div class="card-body p-0">
                                            <x-lucide-zap class="w-10 h-10 text-primary-custom mb-3 mx-auto" />
                                            <div class="h5 fw-bold mb-1">24/7</div>
                                            <div class="small text-muted-custom">Monitoring</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-5 text-center">
        <div class="container-fluid">
            <h2 class="display-4 fw-bold mb-3">Ready to Get Started?</h2>
            <p class="fs-5 text-muted-custom mb-4">
                Join CarePay today and experience the future of banking
            </p>
            <a href="{{ route('register') }}" class="btn btn-gradient btn-lg px-5 py-3 d-inline-flex align-items-center gap-2 text-decoration-none">
                Create Free Account
                <x-lucide-arrow-right class="w-5 h-5" />
            </a>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="border-top mt-5 py-5" style="border-color: rgba(168, 85, 247, 0.2) !important;">
        <div class="container-fluid">
            <div class="row g-4">
                <div class="col-md-6 col-lg-3">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="icon-container icon-container-sm gradient-bg-primary">
                            <x-lucide-wallet class="text-white w-5 h-5" />
                        </div>
                        <span class="fw-bold">CarePay</span>
                    </div>
                    <p class="text-muted-custom small lh-lg">
                        Modern digital banking for everyone
                    </p>
                </div>
                <div class="col-md-6 col-lg-3">
                    <h6 class="fw-bold mb-3">Product</h6>
                    <nav class="d-flex flex-column gap-2">
                        <a href="#features" class="text-muted-custom p-0 small text-decoration-none hover-link">Features</a>
                        <a href="#" class="text-muted-custom p-0 small text-decoration-none hover-link">Security</a>
                        <a href="#" class="text-muted-custom p-0 small text-decoration-none hover-link">Pricing</a>
                    </nav>
                </div>
                <div class="col-md-6 col-lg-3">
                    <h6 class="fw-bold mb-3">Company</h6>
                    <nav class="d-flex flex-column gap-2">
                        <a href="#" class="text-muted-custom p-0 small text-decoration-none hover-link">About</a>
                        <a href="#" class="text-muted-custom p-0 small text-decoration-none hover-link">Careers</a>
                        <a href="#" class="text-muted-custom p-0 small text-decoration-none hover-link">Contact</a>
                    </nav>
                </div>
                <div class="col-md-6 col-lg-3">
                    <h6 class="fw-bold mb-3">Legal</h6>
                    <nav class="d-flex flex-column gap-2">
                        <a href="#" class="text-muted-custom p-0 small text-decoration-none hover-link">Privacy</a>
                        <a href="#" class="text-muted-custom p-0 small text-decoration-none hover-link">Terms</a>
                        <a href="#" class="text-muted-custom p-0 small text-decoration-none hover-link">Compliance</a>
                    </nav>
                </div>
            </div>
            <div class="border-top mt-4 pt-4 text-center" style="border-color: rgba(168, 85, 247, 0.2) !important;">
                <p class="text-muted-custom small mb-0">© {{ now()->year }} CarePay. All rights reserved.</p>
            </div>
        </div>
    </footer>
</div>
