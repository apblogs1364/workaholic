<?php 
$page_title = "Reviews";
include 'header.php'; 
?>

<section class="bg-gray-50 min-h-screen py-16">
    <div class="max-w-5xl mx-auto px-6">

        <!-- PAGE TITLE -->
        <h1 class="text-4xl font-extrabold text-gray-900 text-center mb-10">
            Client <span class="text-blue-600">Reviews</span>
        </h1>

        <!-- OVERALL RATING -->
        <div class="bg-white shadow p-8 rounded-xl text-center mb-12">
            <h2 class="text-2xl font-bold text-gray-900 mb-3">Overall Rating</h2>
            <div class="flex justify-center items-center gap-1 text-yellow-400 text-3xl mb-2">
                ★ ★ ★ ★ ★
            </div>
            <p class="text-gray-600">Based on 148 reviews</p>
        </div>

        <!-- REVIEWS LIST -->
        <div class="space-y-6 mb-16">

            <!-- Review Item -->
            <div class="bg-white shadow p-6 rounded-xl">
                <div class="flex items-center gap-2 text-yellow-400 text-xl mb-1">
                    ★ ★ ★ ★ ★
                </div>
                <h3 class="font-bold text-gray-900 text-lg">Amazing Work!</h3>
                <p class="text-gray-600 mt-2">
                    Delivered my website before deadline with perfect UI. Communication was excellent.
                </p>
                <p class="text-gray-500 text-sm mt-3">— Rahul Sharma</p>
            </div>

            <div class="bg-white shadow p-6 rounded-xl">
                <div class="flex items-center gap-2 text-yellow-400 text-xl mb-1">
                    ★ ★ ★ ★ ☆
                </div>
                <h3 class="font-bold text-gray-900 text-lg">Very Professional</h3>
                <p class="text-gray-600 mt-2">
                    Great work quality and fast response. Would hire again for future projects.
                </p>
                <p class="text-gray-500 text-sm mt-3">— Ayesha Khan</p>
            </div>

            <div class="bg-white shadow p-6 rounded-xl">
                <div class="flex items-center gap-2 text-yellow-400 text-xl mb-1">
                    ★ ★ ★ ☆ ☆
                </div>
                <h3 class="font-bold text-gray-900 text-lg">Good but can improve</h3>
                <p class="text-gray-600 mt-2">
                    Work was great but delivery was slightly delayed. Overall satisfied.
                </p>
                <p class="text-gray-500 text-sm mt-3">— David Patel</p>
            </div>

        </div>

        <!-- WRITE REVIEW FORM -->
        <div class="bg-white shadow-xl p-10 rounded-2xl">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Write a Review</h2>

            <form>

                <!-- Rating Input -->
                <label class="block font-semibold text-gray-900 mb-2">Your Rating</label>
                <div class="flex gap-1 text-3xl text-yellow-400 cursor-pointer mb-4">
                    <span>★</span><span>★</span><span>★</span><span>★</span><span>★</span>
                </div>

                <!-- Name -->
                <label class="block font-semibold text-gray-900 mb-2">Your Name</label>
                <input type="text" class="w-full border border-gray-300 rounded-lg px-4 py-2 mb-4" placeholder="Enter your name">

                <!-- Review Text -->
                <label class="block font-semibold text-gray-900 mb-2">Your Review</label>
                <textarea class="w-full border border-gray-300 rounded-lg px-4 py-3 h-32 mb-6" placeholder="Write your review..."></textarea>

                <!-- Button -->
                <button class="bg-blue-600 text-white px-8 py-3 rounded-xl font-semibold hover:bg-blue-700">
                    Submit Review
                </button>

            </form>
        </div>

    </div>
</section>
<?php include 'footer.php'; ?>
</body>