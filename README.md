[![Start contributing](https://img.shields.io/github/issues/LibreCodeCoop/my_company/good%20first%20issue?color=7057ff&label=Contribute)](https://github.com/LibreCodeCoop/my_company/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc+label%3A%22good+first+issue%22)

# My Company

Get access to important information about your company

## Setup

* Install this app
* Configuration
* go to root folder of your nextcloud instance and run the follow commands:
  ```bash
  # registration
  occ app:enable --force registration
  occ config:app:set registration username_policy_regex --value "/^\d{11}$/"
  occ config:app:set registration show_fullname --value yes
  occ config:app:set registration enforce_fullname --value yes
  occ config:app:set registration additional_hint --value "Informe o seu CPF como nome de usuário utilizando apenas números"
  occ config:app:set core shareapi_allow_links_exclude_groups --value "[\"waiting-approval\"]"
  occ config:app:set core shareapi_only_share_with_group_members --value no

  occ config:app:set files default_quota --value "50 MB"

  occ config:app:set core shareapi_allow_share_dialog_user_enumeration --value no

  # System settings
  # Disable "Log in with a device" at login screen
  occ config:system:set auth.webauthn.enabled --value false --type boolean
  occ config:system:set defaultapp --value my_company

  # Skeleton directory
  mkdir -p data/appdata_`occ config:system:get instanceid`/my_company/skeleton
  occ config:system:set skeletondirectory --value /data/appdata_`occ config:system:get instanceid`/my_company/skeleton

  # Theme
  occ config:app:set theming name --value "LibreCode"
  occ config:app:set theming slogan --value "Feito com ❤️"
  occ config:app:set theming url --value "https://librecode.coop"
  occ config:app:set theming color "#6ea68f"
  occ config:app:set theming logoMime --value "image/png"
  occ config:app:set theming backgroundMime --value "image/jpg"
  mkdir -p data/appdata_`occ config:system:get instanceid`/my_company/theming

  # Group folders
  occ app:enable --force groupfolders
  occ group:add mycompany --display-name="My Company"
  occ groupfolders:create mycompany
  occ groupfolders:group `occ groupfolders:list --output=json|jq '.[]|select(.mount_point=="mycompany")|.id'` mycompany

  # Terms of service
  occ app:enable terms_of_service
  ```
## Theming
* Inside the folder `my_company/theming` you will need go create a folder with the domain of company
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
