<!-- Start Footer 
    ============================================= -->
    <footer class="bg-dark text-light" style="background-image: url({{ asset('assets/img/shape/8.png') }});">
        <div class="container">
            <div class="f-items default-padding">
                <div class="row">

                    <!-- Single Itme -->
                    <div class="col-lg-4 col-md-6 item">
                        <div class="footer-item about">
                            <img class="logo" src="{{ asset('assets/img/plantix-ai-logo.png') }}" alt="Logo">
                            <p>
                                Plantix-AI is transforming Pakistani agriculture with intelligent technology. Get
                                AI-powered crop recommendations, disease detection, and precision farming solutions
                                delivered directly to your mobile device.
                            </p>
                            <form action="#">
                                <input type="email" placeholder="Subscribe for Farming Tips" class="form-control"
                                    name="email" required>
                                <button type="submit"> Go</button>
                            </form>
                        </div>
                    </div>
                    <!-- End Single Itme -->

                    <!-- Single Itme -->
                    <div class="col-lg-2 col-md-6 item">
                        <div class="footer-item link">
                            <h4 class="widget-title">Explore</h4>
                            <ul>
                                <li>
                                    <a href="{{ route('about') }}">About Us</a>
                                </li>
                                <li>
                                    <!-- Removed deleted file link: Meet Our Team -->
                                </li>
                                <li>
                                    <a href="{{ route('blog.single') }}">News & Media</a>
                                </li>
                                <li>
                                    <a href="{{ route('plantix-ai') }}">Plantix-AI</a>
                                </li>
                                <li>
                                    <!-- Removed deleted file link: Contact Us -->
                                </li>
                                <li>
                                    <!-- Removed deleted file link: Volunteers -->
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- End Single Itme -->

                    <!-- Single Itme -->
                    <div class="col-lg-3 col-md-6 item">
                        <div class="footer-item recent-post">
                            <h4 class="widget-title">Recent Posts</h4>
                            <ul>
                                <li>
                                    <div class="thumb">
                                        <a href="{{ route('blog.single') }}">
                                            <img src="{{ asset('assets/img/800x800.png') }}" alt="Thumb">
                                        </a>
                                    </div>
                                    <div class="info">
                                        <div class="meta-title">
                                            <span class="post-date">12 Sep, 2025</span>
                                        </div>
                                        <h5><a href="{{ route('blog.single') }}">How AI Detects Cotton Leaf Curl
                                                Early</a></h5>
                                    </div>
                                </li>
                                <li>
                                    <div class="thumb">
                                        <a href="{{ route('blog.single') }}">
                                            <img src="{{ asset('assets/img/fertilizer.png') }}" alt="Thumb">
                                        </a>
                                    </div>
                                    <div class="info">
                                        <div class="meta-title">
                                            <span class="post-date">18 Jul, 2025</span>
                                        </div>
                                        <h5><a href="{{ route('blog.single') }}">Smart Fertilizer Plans Save 30% on
                                                Costs</a></h5>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- End Single Itme -->

                    <!-- Single Itme -->
                    <div class="col-lg-3 col-md-6 item">
                        <div class="footer-item contact">
                            <h4 class="widget-title">Contact Info</h4>
                            <ul>
                                <li>
                                    <div class="icon">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div class="content">
                                        <strong>Address:</strong>
                                        GIMS, Gujrat, Punjab
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fas fa-envelope"></i>
                                    </div>
                                    <div class="content">
                                        <strong>Email:</strong>
                                        <a href="mailto:info@plantixai.com">info@plantixai.com</a>
                                    </div>
                                </li>
                                <li>
                                    <div class="icon">
                                        <i class="fas fa-phone"></i>
                                    </div>
                                    <div class="content">
                                        <strong>Phone:</strong>
                                        <a href="tel:2151234567">+123 34598768</a>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- Single Itme -->

                </div>
            </div>

        </div>
        <div class="shape-right-bottom">
            <img src="{{ asset('assets/img/shape/7.png') }}" alt="Image Not Found">
        </div>
    </footer>
