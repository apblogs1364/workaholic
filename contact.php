<?php 
$page_title = "Contact";
include 'header.php'; 
?>

<!-- Breadcrumb -->
<div class="max-w-4xl mx-auto px-6 mt-6 text-sm text-gray-500">
    <a href="index.php" class="text-gray-600 hover:underline">Home</a> /
    <span class="text-gray-700 font-medium">Contact Us</span>
</div>

<!-- Hero Image -->
<div class="relative overflow-hidden max-w-4xl mx-auto px-6 mt-6">
    <div id="heroImage" class="w-full h-80 md:h-96 bg-cover bg-center rounded-xl shadow-md transition-all duration-1000"
        style="background-image: url('Images/freelancer_Prostock-Studio_getty.jpg');">
    </div>
</div>

<section class="max-w-4xl mx-auto px-6 py-12 text-gray-800 leading-relaxed opacity-100 transition-opacity duration-700">
    <h1 class="text-center text-4xl md:text-5xl font-bold text-[#0F172A] mb-6">Get in Touch</h1>
    <p class="mb-12 text-gray-600 text-center max-w-2xl mx-auto">
        Have questions? Fill out the form below and our team will get back to you within <strong>24–48 hours</strong>.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">

        <!-- Left Side Contact Info -->
        <div class="space-y-6 bg-gray-50 rounded-2xl shadow-sm p-8 border border-gray-200">
            <h2 class="text-2xl font-semibold mb-4 text-[#0F172A]">Contact Information</h2>
            <p class="text-gray-600">We’d love to hear from you! Reach us via the form or using the details below.</p>

            <div class="flex items-center space-x-4">
                <span class="bg-blue-100 text-blue-600 p-3 rounded-full">📍</span>
                <p class="text-gray-700">811, University Road, Rajkot</p>
            </div>

            <div class="flex items-center space-x-4">
                <span class="bg-blue-100 text-blue-600 p-3 rounded-full">📧</span>
                <a href="mailto:workaholic847282@gmail.com" class="text-blue-600 hover:underline">workaholic847282@gmail.com</a>
            </div>

            <div class="flex items-center space-x-4">
                <span class="bg-blue-100 text-blue-600 p-3 rounded-full">📞</span>
                <p class="text-gray-700">+91 9898008XXX</p>
            </div>
        </div>




        <!-- Right Contact Form -->
        <form id="contactForm" action="contact-us.php" method="POST"
            class="space-y-5 bg-white shadow-md rounded-2xl p-8 transition transform hover:scale-[1.01] border border-gray-200">

            <!-- Full Name -->
            <div>
                <label for="name" class="block font-medium mb-1 text-[#0F172A]">Full Name</label>
                <input type="text" id="name" name="name"
                    data-validation="required alpha min" data-min="2"
                    class="w-full border border-gray-300 bg-white text-gray-800 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-700 focus:outline-none transition">
                <div class="error" id="nameError"></div>
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block font-medium mb-1 text-[#0F172A]">Email</label>
                <input type="text" id="email" name="email"
                    data-validation="required email"
                    class="w-full border border-gray-300 bg-white text-gray-800 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-700 focus:outline-none transition">
                <div class="error" id="emailError"></div>
            </div>

            <!-- Subject -->
            <div>
                <label for="subject" class="block font-medium mb-1 text-[#0F172A]">Subject</label>
                <input type="text" id="subject" name="subject"
                    data-validation="required min" data-min="3"
                    class="w-full border border-gray-300 bg-white text-gray-800 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-700 focus:outline-none transition">
                <div class="error" id="subjectError"></div>
            </div>

            <!-- Message -->
            <div>
                <label for="message" class="block font-medium mb-1 text-[#0F172A]">Message</label>
                <textarea id="message" name="message" rows="5"
                    data-validation="required min" data-min="10"
                    class="w-full border border-gray-300 bg-white text-gray-800 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-700 focus:outline-none transition"></textarea>
                <div class="error" id="messageError"></div>
            </div>

            <!-- Submit -->
            <button type="submit"
                class="w-full  bg-indigo-600
                               text-white font-bold px-6 py-3 rounded-lg shadow-md transition transform hover:scale-105">
                Send Message
            </button>
        </form>
    </div>
</section>

</div>

<?php include 'footer.php'; ?>
</body>