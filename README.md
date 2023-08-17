[![Start contributing](https://img.shields.io/github/issues/LibreCodeCoop/my_company/good%20first%20issue?color=7057ff&label=Contribute)](https://github.com/LibreCodeCoop/my_company/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc+label%3A%22good+first+issue%22)

# My Company

Get access to important information about your company

## Setup

* Install this app
* Configuration
* go to root folder of your nextcloud instance and run the follow commands:
  ```bash
  # Group folders
  occ app:enable --force groupfolders
  occ group:add mycompany --display-name="My Company"

  occ app:enable my_company
  occ my-company:company:add --code local --name "My company" --domain local.localhost

  # registration
  occ app:enable --force registration
  occ config:app:set registration show_fullname --value yes
  occ config:app:set registration email_is_optional --value no
  occ config:app:set registration disable_email_verification --value no
  occ config:app:set registration enforce_fullname --value yes
  occ config:app:set registration registered_user_group --value "waiting-approval"
  occ config:app:set core shareapi_allow_links_exclude_groups --value "[\"waiting-approval\"]"
  occ config:app:set core shareapi_only_share_with_group_members --value no

  occ config:app:set files default_quota --value "50 MB"

  occ config:app:set core shareapi_allow_share_dialog_user_enumeration --value no

  # System settings
  # Disable "Log in with a device" at login screen
  occ config:system:set auth.webauthn.enabled --value false --type boolean
  occ config:system:set defaultapp --value my_company
  occ config:system:set auth.bruteforce.protection.enabled --value false --type boolean
  occ config:app:set password_policy minLength --value 8
  occ config:system:set force_language --value en
  occ config:system:set knowledgebaseenabled --value false --type boolean

  # Skeleton directory
  # First, go to root folder of Nextcloud
  mkdir -p data/appdata_`occ config:system:get instanceid`/my_company/skeleton
  occ config:system:set skeletondirectory --value /data/appdata_`occ config:system:get instanceid`/my_company/skeleton

  # Theme
  occ config:app:set theming name --value "My Company"
  occ config:app:set theming slogan --value "Made with ❤️"
  occ config:app:set theming url --value "https://mycompany.coop"
  occ config:app:set theming color --value "#0082c9"
  occ config:app:set theming logoMime --value "image/png"
  occ config:app:set theming backgroundMime --value "image/jpg"

  # Forms
  git clone --depth 1 --branch feat/embedded https://github.com/vitormattos/forms/ apps/forms
  docker run -it -v ${PWD}apps/forms:/app -w /app node npm ci
  docker run -it -v ${PWD}apps/forms:/app -w /app node npm run build
  occ app:enable --force forms
  # Create first the form and get the ID to use here
  occ config:app:set my_company registration_form_id --value 1

  # Terms of service
  occ app:enable --force terms_of_service

  # LibreSign
  git clone --depth 1 --branch feature/add-sign-method https://github.com/LibreSign/libresign/ apps/libresign
  occ app:enable --force libresign
  ```
## Theming
* Inside the folder `appdata_<instanceId>/my_company/theming` you will need go create a folder with the domain of company
* Inside the folder of company, create the file `background` and `logo` without extension.
  > Logo need to be PNG and background need to be PNG  to follow the defined at `theming` app at `logoMime` and `backgroundMime` setting
* Refresh the cache of app data folder to update the metadata of new images:
  ```bash
  occ files:scan-app-data
  ```

# Terms of service
* Fill the terms of service at `/settings/admin/terms_of_service`

## Contributing

[here](.github/CONTRIBUTING.md)
