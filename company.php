<?php
$page_title = "Company";
include 'header.php';
?>

<section class="bg-gray-100 py-16">
    <div class="max-w-6xl mx-auto px-6">

        <h1 class="text-4xl font-extrabold text-center mb-12">
            Our <span class="text-blue-600">Company</span>
        </h1>

        <!-- Overview -->
        <div class="bg-white shadow-lg p-10 rounded-xl mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Company Overview</h2>
            <p class="text-gray-700 leading-relaxed">
                Workaholic is a digital solutions provider delivering world-class web,
                app, branding, and freelancing services across the globe. Trusted by
                clients and businesses of all sizes.
            </p>
        </div>

        <!-- Workflow -->
        <h2 class="text-3xl font-bold text-center mb-6">How We Work</h2>
        <div class="grid md:grid-cols-4 gap-8">

            <div class="bg-white shadow p-6 rounded-xl text-center">
                <h3 class="font-bold text-xl mb-2">1. Discussion</h3>
                <p class="text-gray-600">Understanding your requirements.</p>
            </div>

            <div class="bg-white shadow p-6 rounded-xl text-center">
                <h3 class="font-bold text-xl mb-2">2. Planning</h3>
                <p class="text-gray-600">Project roadmap & strategy.</p>
            </div>

            <div class="bg-white shadow p-6 rounded-xl text-center">
                <h3 class="font-bold text-xl mb-2">3. Execution</h3>
                <p class="text-gray-600">Designing & developing the product.</p>
            </div>

            <div class="bg-white shadow p-6 rounded-xl text-center">
                <h3 class="font-bold text-xl mb-2">4. Delivery</h3>
                <p class="text-gray-600">Testing, deployment & support.</p>
            </div>

        </div>

        <!-- CTA -->
        <div class="text-center mt-12">
            <a href="login.php"
                class="inline-block bg-blue-600 text-white px-8 py-3 rounded-xl text-lg font-semibold hover:bg-blue-700">
                Join as Company
            </a>
        </div>

    </div>
</section>


<?php include 'footer.php'; ?>
</body>