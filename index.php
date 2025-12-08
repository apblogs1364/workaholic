<?php include 'header.php'; ?>

<!-- Main content placeholder -->
<main class="max-w-7xl mx-auto p-6">
  <h1 class="text-3xl font-bold text-gray-900">Welcome to Workaholic</h1>
  <p class="mt-4 text-gray-700">Your freelancer marketplace.</p>
</main>

<!-- Hero Section -->
<section class="bg-white">
  <div class="max-w-7xl mx-auto px-6 py-20 lg:flex lg:items-center lg:gap-12">
    <!-- Text Content -->
    <div class="max-w-xl text-center mx-auto lg:mx-0 lg:text-left">
      <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl">
        Connect with Top Freelancers <br />
        <span class="text-indigo-600">For Your Next Big Project</span>
      </h1>
      <p class="mt-6 text-lg leading-8 text-gray-600">
        Workaholic is your ultimate freelance marketplace — hire skilled professionals or offer your expertise to clients worldwide.
      </p>
      <div class="mt-10 flex flex-col gap-4 sm:flex-row sm:justify-start">
        <a href="freelancer.php" class="inline-block rounded-md bg-indigo-600 px-6 py-3 text-white text-lg font-semibold hover:bg-indigo-700 transition">
          Visit Freelancers
        </a>
        <a href="company.php" class="inline-block rounded-md border border-indigo-600 px-6 py-3 text-indigo-600 text-lg font-semibold hover:bg-indigo-50 transition">
          Visit Company
        </a>
      </div>
    </div>

    <!-- Image/Illustration -->
    <div class="mt-12 lg:mt-0 flex justify-center lg:justify-end">
      <img
        src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&w=600&q=80"
        alt="Freelancer working on laptop"
        class="w-full max-w-md rounded-lg shadow-lg"
        loading="lazy" />
    </div>
  </div>
</section>

<!-- Featured Companies Section -->
<section class="bg-gray-50 py-16">
  <div class="max-w-7xl mx-auto px-6">
    <h2 class="text-3xl font-bold text-gray-900 mb-12 text-center">
      Trusted Companies Using Workaholic
    </h2>

    <div class="grid gap-10 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
      <!-- Company Card -->
      <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center">
        <img
          src="https://logo.clearbit.com/upwork.com"
          alt="Upwork Logo"
          class="h-16 mb-4 object-contain"
          loading="lazy" />
        <h3 class="text-xl font-semibold mb-2">Upwork</h3>
        <p class="text-gray-600 text-sm">
          One of the largest freelance marketplaces connecting millions of clients and freelancers worldwide.
        </p>
      </div>

      <!-- Company Card -->
      <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center">
        <img
          src="https://logo.clearbit.com/fiverr.com"
          alt="Fiverr Logo"
          class="h-16 mb-4 object-contain"
          loading="lazy" />
        <h3 class="text-xl font-semibold mb-2">Fiverr</h3>
        <p class="text-gray-600 text-sm">
          A creative marketplace empowering freelancers to sell their services starting at $5.
        </p>
      </div>

      <!-- Company Card -->
      <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center">
        <img
          src="https://logo.clearbit.com/toptal.com"
          alt="Toptal Logo"
          class="h-16 mb-4 object-contain"
          loading="lazy" />
        <h3 class="text-xl font-semibold mb-2">Toptal</h3>
        <p class="text-gray-600 text-sm">
          Exclusive network of the top 3% of freelance talent in software development, design, and finance.
        </p>
      </div>

      <!-- Company Card -->
      <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center">
        <img
          src="https://logo.clearbit.com/99designs.com"
          alt="99designs Logo"
          class="h-16 mb-4 object-contain"
          loading="lazy" />
        <h3 class="text-xl font-semibold mb-2">99designs</h3>
        <p class="text-gray-600 text-sm">
          The world’s leading platform for graphic design freelancers to connect with clients.
        </p>
      </div>

      <!-- Company Card -->
      <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center">
        <img
          src="https://logo.clearbit.com/freelancer.com"
          alt="Freelancer.com Logo"
          class="h-16 mb-4 object-contain"
          loading="lazy" />
        <h3 class="text-xl font-semibold mb-2">Freelancer.com</h3>
        <p class="text-gray-600 text-sm">
          A global freelancing platform offering diverse job categories and contests.
        </p>
      </div>

      <!-- Company Card -->
      <div class="bg-white rounded-lg shadow-md p-6 flex flex-col items-center text-center">
        <img
          src="https://logo.clearbit.com/peopleperhour.com"
          alt="PeoplePerHour Logo"
          class="h-16 mb-4 object-contain"
          loading="lazy" />
        <h3 class="text-xl font-semibold mb-2">PeoplePerHour</h3>
        <p class="text-gray-600 text-sm">
          Connecting businesses to expert freelancers who get work done quickly and efficiently.
        </p>
      </div>
    </div>
  </div>
</section>

<?php include 'footer.php'; ?>
</body>
