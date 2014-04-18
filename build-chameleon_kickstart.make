api = 2
core = 7.x
includes[] = drupal-org-core.make

; Profile =====================================================================
projects[chameleon_kickstart][type] = "profile"
projects[chameleon_kickstart][download][type] = "git"
projects[chameleon_kickstart][download][url] = "https://github.com/katbailey/personalize-behat-tests.git"

; Modules =====================================================================

; Contrib module: personalize
; ---------------------------------------
projects[personalize][type] = "module"
projects[personalize][download][type] = "git"
projects[personalize][download][url] = "http://git.drupal.org/project/personalize.git"
projects[personalize][download][branch] = "7.x-1.x"
projects[personalize][subdir] = contrib

; Contrib module: visitor_actions
; ---------------------------------------
projects[visitor_actions][type] = "module"
projects[visitor_actions][download][type] = "git"
projects[visitor_actions][download][url] = "http://git.drupal.org/project/visitor_actions.git"
projects[visitor_actions][download][branch] = "7.x-1.x"
projects[visitor_actions][subdir] = contrib

; Contrib module: acquia_lift
; ---------------------------------------
projects[acquia_lift][type] = "module"
projects[acquia_lift][download][type] = "git"
projects[acquia_lift][download][url] = "http://git.drupal.org/project/acquia_lift.git"
projects[acquia_lift][download][branch] = "7.x-1.x"
projects[acquia_lift][subdir] = contrib


; Contrib module: ctools
; ---------------------------------------
projects[ctools][subdir] = contrib
projects[ctools][version] = 1.4

; Contrib module: libraries
; ---------------------------------------
projects[libraries][version] = "2.2"


; Contrib module: navbar
; ---------------------------------------
projects[navbar][subdir] = contrib
projects[navbar][version] = 1.x-dev


; Libraries ======================================================================

; Library: backbone
; ---------------------------------------
libraries[backbone][destination] = "libraries"
libraries[backbone][download][type] = "get"
libraries[backbone][download][url] = "http://backbonejs.org/backbone-min.js"
libraries[backbone][directory] = "backbone"

; Library: underscore
; ---------------------------------------
libraries[underscore][destination] = "libraries"
libraries[underscore][download][type] = "get"
libraries[underscore][download][url] = "http://underscorejs.org/underscore-min.js"
libraries[underscore][directory] = "underscore"

; Library: chosen
; ---------------------------------------
libraries[chosen][destination] = "libraries"
libraries[chosen][download][type] = "get"
libraries[chosen][download][url] = "https://github.com/harvesthq/chosen/releases/download/1.0.0/chosen_v1.0.0.zip"
libraries[chosen][directory] = "chosen"

; Themes ======================================================================

projects[ember][version] = 2.0-alpha2
