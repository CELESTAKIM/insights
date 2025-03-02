<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['submit_insight'])) {
    $insight = trim($_POST['insight']);
    $lat = floatval($_POST['latitude']);
    $lng = floatval($_POST['longitude']);
    $user_id = $_SESSION['user_id'];

    if (!empty($insight) && $lat && $lng) {
        $query = "INSERT INTO insights (user_id, insight, latitude, longitude) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "isdd", $user_id, $insight, $lat, $lng);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

if (isset($_POST['update_profile'])) {
    $new_email = trim($_POST['email']);
    $new_username = trim($_POST['username']);
    $new_phone = !empty($_POST['phone']) ? trim($_POST['phone']) : NULL;
    $user_id = $_SESSION['user_id'];
    $profile_image = $user['profile_image'] ?? 'uploads/profile/default.jpg';

    if (filter_var($new_email, FILTER_VALIDATE_EMAIL) && !empty($new_username)) {
        if (!empty($_FILES['profile_image']['name'])) {
            $upload_dir = 'uploads/profile/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file = $_FILES['profile_image'];
            if (in_array($file['type'], $allowed_types)) {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $profile_image = $upload_dir . $user_id . '_profile_' . time() . '.' . $ext;
                move_uploaded_file($file['tmp_name'], $profile_image);
            }
        }

        $query = "UPDATE users SET email = ?, username = ?, phone = ?, profile_image = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $new_email, $new_username, $new_phone, $profile_image, $user_id);
        mysqli_stmt_execute($stmt);
        $_SESSION['username'] = $new_username;
        mysqli_stmt_close($stmt);
    }
}

$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($db, $user_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$gisActivities = [
    "Mapping Population Density", "Analyzing Traffic Patterns", "Tracking Deforestation", "Urban Heat Island Analysis",
    "Flood Risk Assessment", "Wildlife Habitat Mapping", "Soil Erosion Modeling", "Air Quality Monitoring",
    "Crime Hotspot Analysis", "Public Transport Optimization", "Land Use Planning", "Water Resource Management",
    "Geological Fault Mapping", "Agricultural Yield Prediction", "Coastal Erosion Tracking", "Disaster Response Mapping",
    "Historical Site Preservation", "Climate Change Impact Study", "Renewable Energy Site Selection", "Noise Pollution Mapping",
    "Epidemiology Tracking", "Infrastructure Vulnerability", "Tourism Route Planning", "Vegetation Index Analysis",
    "Groundwater Contamination", "Socioeconomic Disparity Mapping", "Road Network Analysis", "Fire Risk Assessment",
    "Wetland Conservation", "Seismic Activity Monitoring", "Energy Consumption Patterns", "Watershed Management",
    "Port Accessibility Study", "Retail Location Analysis", "Pipeline Route Planning", "Forest Fire Prediction",
    "Heatwave Vulnerability", "Cultural Heritage Mapping", "Snow Cover Analysis", "Solar Potential Mapping",
    "Wind Farm Siting", "Urban Sprawl Tracking", "Fisheries Management", "Volcanic Hazard Mapping",
    "Railway Expansion Study", "Green Space Accessibility", "Drought Monitoring", "Landslide Risk Mapping",
    "Biodiversity Hotspots", "Industrial Pollution Tracking", "Recycling Facility Placement", "Tsunami Impact Zones",
    "Smart City Planning", "Archaeological Site Detection"
];

$uploadedImages = [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>GIS Insights</title>
    <link rel="stylesheet" href="vendor/leaflet/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-image: url('https://images.unsplash.com/photo-1451187580459-43490279c0fa?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            margin: 20px auto;
            padding: 20px;
            max-width: 1200px;
            flex: 1;
        }

        .profile-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 20px 0;
        }

        .welcome-banner {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(128, 128, 128, 0.8);
            padding: 20px 40px;
            border-radius: 10px;
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            z-index: 1000;
            animation: fadeIn 1s forwards;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-width: 500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }

        .welcome-banner.minimized {
            padding: 10px;
            min-width: auto;
            font-size: 16px;
        }

        .welcome-banner.minimized span {
            display: none;
        }

        .minimize-btn, .close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            margin-left: 20px;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        footer {
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            position: relative;
            width: 100%;
            z-index: 1;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .welcome-banner {
                min-width: 90%;
                font-size: 18px;
                padding: 15px;
            }

            .container {
                margin: 10px;
                padding: 15px;
            }

            .nav-menu {
                display: none;
                position: absolute;
                top: 60px;
                left: 0;
                right: 0;
                background-color: #fff;
                flex-direction: column;
                width: 100%;
            }

            .nav-menu.active {
                display: flex;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .profile-section {
                padding: 20px;
            }

            .dashboard-grid > div {
                padding: 10px;
            }

            #map {
                height: 300px !important;
            }
        }

        @media (max-width: 480px) {
            .welcome-banner {
                font-size: 14px;
                padding: 10px;
            }

            #map {
                height: 200px !important;
            }

            .profile-section {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-banner" id="welcomeBanner">
        <span>Welcome to GIS Insights! Share your geospatial discoveries and explore GIS applications.</span>
        <button class="minimize-btn" onclick="toggleMinimize()">-</button>
        <button class="close-btn" onclick="closeBanner()">×</button>
    </div>

    <div class="container">
        <header>
            <div class="header-content">
                <span class="brand-mobile">CELESTA KIM GIS Insights</span>
                <button class="hamburger" onclick="toggleMenu()">☰</button>
                <nav class="nav-menu">
                    <a href="index.php">GIS Insights</a>
                    <div class="dropdown">
                        <a href="explore.php">Explore</a>
                        <div class="dropdown-content">
                            <!-- Analyze demographic distribution patterns across regions -->
                            <a href="explore.php#population">Population Density</a>
                            <!-- Study vehicle flow and congestion in urban areas -->
                            <a href="explore.php#traffic">Traffic Patterns</a>
                            <!-- Monitor forest cover changes over time -->
                            <a href="explore.php#deforestation">Deforestation Tracking</a>
                            <!-- Assess areas prone to flooding based on topography -->
                            <a href="explore.php#flood">Flood Risk</a>
                            <!-- Map and protect animal ecosystems -->
                            <a href="explore.php#wildlife">Wildlife Habitats</a>
                        </div>
                    </div>
                    <a href="about.php">About</a>
                    <a href="portfolio.php">Portfolio</a>
                    <a href="insight.php">Insights</a>
                    <a href="blog.php">Blog</a>
                    <a href="index.php#dashboard">Dashboard</a>
                    <a href="index.php#user-profile">Your Profile</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <div class="dashboard" id="dashboard">
                <h2>Dashboard</h2>
                <div class="dashboard-grid">
                    <div style="position: relative;">
                        <h3>Dedan Kimathi University Location</h3>
                        <div id="map" style="height: 400px;"></div>
                        <select id="map-type-selector" onchange="changeMapType(this.value)">
                            <option value="osm">OpenStreetMap</option>
                            <option value="esri-topo">ESRI Topographic</option>
                            <option value="esri-satellite">ESRI Satellite</option>
                            <option value="stamen-terrain">Stamen Terrain</option>
                        </select>
                        <p>Real-time GIS map centered on DeKUT, Nyeri, Kenya.</p>
                    </div>
                </div>
            </div>

            <div class="form-container insight-form" id="insight-form">
                <h2>Share an Insight</h2>
                <p>Click the map to set a location, then add your insight below:</p>
                <form method="POST" action="index.php">
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <textarea name="insight" placeholder="Your insight" rows="4" required></textarea><br>
                    <button type="submit" name="submit_insight">Submit</button>
                </form>
            </div>

            <div class="profile-section" id="user-profile">
                <h2>Your Professional Profile</h2>
                <div class="profile-info">
                    <?php if (!empty($user['profile_image']) && file_exists($user['profile_image'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Picture" style="width: 150px; height: 150px; border-radius: 50%; margin: 0 auto 20px; display: block; object-fit: cover; border: 3px solid #3498db;">
                    <?php else: ?>
                        <div style="width: 150px; height: 150px; border-radius: 50%; background-color: #ddd; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; color: #666; border: 3px solid #3498db;">No Profile Picture</div>
                    <?php endif; ?>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'Not provided'); ?></p>
                    <p><strong>Country:</strong> <?php echo htmlspecialchars($user['country']); ?></p>
                    <p><strong>Student:</strong> <?php echo $user['is_student'] === 'yes' ? 'Yes, ' . htmlspecialchars($user['university']) : 'No, ' . htmlspecialchars($user['non_student_role']); ?></p>
                    <p><strong>Referral:</strong> <?php echo htmlspecialchars($user['referral_source']) . ($user['referral_details'] ? ' - ' . htmlspecialchars($user['referral_details']) : ''); ?></p>
                </div>
                <button onclick="document.getElementById('edit-profile-form').classList.toggle('hidden')">Edit Profile</button>
                <div id="edit-profile-form" class="hidden">
                    <form method="POST" action="index.php" enctype="multipart/form-data">
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required><br>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Phone (optional)"><br>
                        <label for="profile_image">Profile Picture:</label><br>
                        <input type="file" name="profile_image" id="profile_image" accept="image/*"><br>
                        <button type="submit" name="update_profile">Save Changes</button>
                    </form>
                </div>
            </div>

            <div class="gis-activities" id="gis-activities">
                <h2>GIS Activities</h2>
                <ul id="activities-list">
                    <?php foreach ($gisActivities as $index => $activity): ?>
                        <li>
                            <a href='#activity-<?php echo $index; ?>' onclick='showActivityDetail("<?php echo htmlspecialchars($activity); ?>", <?php echo $index; ?>)'>
                                <?php echo htmlspecialchars($activity); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="activity-detail hidden" id="activity-detail"></div>

            <div class="video-section" id="video-section">
                <h2>Learn GIS</h2>
                <div><h3>Introduction to GIS</h3><iframe src="https://www.youtube.com/embed/7beIAyjhWKU" frameborder="0" allowfullscreen></iframe></div>
                <div><h3>Georeferencing</h3><iframe src="https://www.youtube.com/embed/tXsTCkFkGIs" frameborder="0" allowfullscreen></iframe></div>
                <div><h3>Interpolation</h3><iframe src="https://www.youtube.com/embed/wWW7GEv-Ais" frameborder="0" allowfullscreen></iframe></div>
            </div>
        </main>

        <footer>
            <div class="footer-content">
                <nav>
                    <p style="font-family: 'Inconsolata', monospace;">GIS Insights</p>
                    <a href="about.php">About</a>
                    <a href="terms.php">Terms</a>
                    <a href="blog.php">Blog</a>
                </nav>
                <div>
                    <p style="font-family: 'Inconsolata', monospace;">© 2025 GIS Insights</p>
                    <p style="font-family: 'Inconsolata', monospace;">Created by: Kimathi Joram - GIS Enthusiast at Dedan Kimathi University</p>
                    <div class="social-links">
                        <a href="https://x.com/celestakim018" target="_blank" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://www.youtube.com/@CELESTAKIM_GIS" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <a href="https://www.linkedin.com/in/celesta-kim-21020232b/" target="_blank" aria-label="LinkedIn"><i class="fab fa-linkedin"></i></a>
                        <a href="https://github.com/CELESTAKIM" target="_blank" aria-label="GitHub"><i class="fab fa-github"></i></a>
                        <a href="https://www.facebook.com/Eng.CelestaKim/" target="_blank" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="https://www.instagram.com/celestakim_gis/" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.pinterest.com/celestakim018/" target="_blank" aria-label="Pinterest"><i class="fab fa-pinterest"></i></a>
                        <a href="https://mastodon.social/@CELESTAKIM_GIS" target="_blank" aria-label="Mastodon"><i class="fas fa-globe"></i></a>
                    </div>
                    <div class="partnerships">
                        <p>Partnerships: 
                            <a href="https://x.com/GeospatialDev">GDeVDeKUT</a>|
                        </p>
                    </div>
                    <button id="download-btn" onclick="downloadImages()">Download Images</button>
                    <div class="hidden-upload hidden">
                        <form method="POST" action="upload_explore.php">
                            <input type="email" name="admin_email" placeholder="Admin Email" required>
                            <input type="file" name="explore_file" accept="video/*,image/*" required>
                            <button type="submit">Upload</button>
                        </form>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script src="vendor/leaflet/leaflet.js"></script>
    <script>
        let map, marker, currentLayer;
        const gisActivities = <?php echo json_encode($gisActivities); ?>;
        const uploadedImages = <?php echo json_encode($uploadedImages); ?>;
        const loggedIn = true;

        function initMap() {
            try {
                if (typeof L === 'undefined') throw new Error("Leaflet not loaded");
                map = L.map('map').setView([-0.3976, 36.9570], 15);
                
                currentLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(map);

                L.marker([-0.3976, 36.9610]).addTo(map)
                    .bindPopup("DeKUT - CelestaKim's Live Location")
                    .openPopup();

                map.on('click', function(e) {
                    if (loggedIn) {
                        if (marker) map.removeLayer(marker);
                        marker = L.marker([e.latlng.lat, e.latlng.lng])
                            .addTo(map)
                            .bindPopup("Insight location")
                            .openPopup();
                        document.getElementById('latitude').value = e.latlng.lat;
                        document.getElementById('longitude').value = e.latlng.lng;
                    }
                });
            } catch (error) {
                console.error("Map initialization error:", error);
                document.getElementById('map').innerHTML = "<p style='color: red;'>Error: Unable to initialize map.</p>";
            }
        }

        function changeMapType(type) {
            if (currentLayer) {
                map.removeLayer(currentLayer);
            }

            switch(type) {
                case 'osm':
                    currentLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '© OpenStreetMap'
                    });
                    break;
                case 'esri-topo':
                    currentLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19,
                        attribution: 'Tiles © Esri'
                    });
                    break;
                case 'esri-satellite':
                    currentLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                        maxZoom: 19,
                        attribution: 'Tiles © Esri'
                    });
                    break;
            }
            currentLayer.addTo(map);
        }

        initMap();

        function showActivityDetail(activity, index) {
            const detail = document.getElementById('activity-detail');
            detail.innerHTML = `
                <h2>${activity}</h2>
                <p>Explore applications, tools, and methodologies for ${activity} in GIS.</p>
                <p><strong>Example:</strong> Using ${activity.toLowerCase()} to enhance urban planning.</p>
                <button onclick="document.getElementById('activity-detail').classList.add('hidden')">Close</button>
            `;
            detail.classList.remove('hidden');
            detail.scrollIntoView({ behavior: 'smooth' });
        }

        function downloadImages() {
            const allImages = [...uploadedImages];
            const textContent = allImages.map(img => `${img.name}: ${img.url}`).join('\n');
            const blob = new Blob([textContent], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'gis_insights_images.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        function toggleMenu() {
            const nav = document.querySelector('.nav-menu');
            nav.classList.toggle('active');
        }

        function toggleMinimize() {
            const banner = document.getElementById('welcomeBanner');
            banner.classList.toggle('minimized');
            const btn = banner.querySelector('.minimize-btn');
            btn.textContent = banner.classList.contains('minimized') ? '+' : '-';
        }

        function closeBanner() {
            const banner = document.getElementById('welcomeBanner');
            banner.style.display = 'none';
        }

        document.querySelector('.nav-menu').addEventListener('mouseleave', function() {
            if (window.innerWidth <= 768) this.classList.remove('active');
        });
    </script>
</body>
</html>