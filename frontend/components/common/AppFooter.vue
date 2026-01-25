<template>
  <footer class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800">
    <div class="container mx-auto px-4 py-12">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
        <!-- About -->
        <div class="col-span-2 md:col-span-1">
          <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary-600 to-primary-500 flex items-center justify-center">
              <span class="text-white font-bold text-lg">ไ</span>
            </div>
            <span class="font-bold text-lg text-gray-900 dark:text-white">ThaiDevDocs</span>
          </div>
          <p class="text-gray-600 dark:text-gray-400 text-sm">
            แหล่งความรู้ด้านการพัฒนาซอฟต์แวร์ภาษาไทยที่ครบถ้วน เข้าถึงง่าย และทันสมัยที่สุด
          </p>
        </div>

        <!-- Documentation -->
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-4">เอกสาร</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <NuxtLink to="/docs/html" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                HTML
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/docs/css" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                CSS
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/docs/javascript" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                JavaScript
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/docs/web-apis" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                Web APIs
              </NuxtLink>
            </li>
          </ul>
        </div>

        <!-- Learn -->
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-4">เรียนรู้</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <NuxtLink to="/learn" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                เส้นทางการเรียนรู้
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/glossary" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                คำศัพท์
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/guides" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                คู่มือ
              </NuxtLink>
            </li>
          </ul>
        </div>

        <!-- Community -->
        <div>
          <h3 class="font-semibold text-gray-900 dark:text-white mb-4">ชุมชน</h3>
          <ul class="space-y-2 text-sm">
            <li>
              <a href="https://github.com/thaidevdocs" target="_blank" rel="noopener" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                GitHub
              </a>
            </li>
            <li>
              <NuxtLink to="/contribute" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                มีส่วนร่วม
              </NuxtLink>
            </li>
            <li>
              <NuxtLink to="/about" class="text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                เกี่ยวกับเรา
              </NuxtLink>
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom -->
      <div class="mt-12 pt-8 border-t border-gray-200 dark:border-gray-800 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          © {{ new Date().getFullYear() }} ThaiDevDocs. สร้างด้วย ❤️ สำหรับนักพัฒนาไทย
        </p>
        <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
          <NuxtLink to="/privacy" class="hover:text-gray-900 dark:hover:text-white">
            นโยบายความเป็นส่วนตัว
          </NuxtLink>
          <NuxtLink to="/terms" class="hover:text-gray-900 dark:hover:text-white">
            ข้อกำหนดการใช้งาน
          </NuxtLink>
        </div>
      </div>
    </div>
  </footer>
</template>
