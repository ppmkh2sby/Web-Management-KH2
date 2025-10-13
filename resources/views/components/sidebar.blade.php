<div class="flex h-screen bg-gray-100">

		<!-- Mobile menu toggle button -->
		<input type="checkbox" id="menu-toggle" class="hidden peer">

		<!-- Sidebar -->
		<div class="hidden peer-checked:flex md:flex flex-col w-64 bg-gray-800 transition-all duration-300 ease-in-out">
			<div class="flex items-center justify-between h-16 bg-gray-900 px-4">
				<span class="text-white font-bold uppercase">Selamat Datang</span>
				<label for="menu-toggle" class="text-white cursor-pointer">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 lg:hidden" fill="none" viewBox="0 0 24 24"
						stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
							d="M6 18L18 6M6 6l12 12" />
					</svg>
				</label>
				<!-- <span class="text-white font-bold uppercase">Sidebar</span> -->
			</div>
			<div class="flex flex-col flex-1 overflow-y-auto">
				<x-navbar></x-navbar>
			</div>
		</div>

		<!-- Main content -->
		<div class="flex flex-col flex-1 overflow-y-auto">
		</div>
	</div>