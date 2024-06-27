## General
AzusaCoordinates is a Pocketmine plug-in that works to make leaves and fields indestructible.

## Features
- Display the current coordinates of the player
- Display the coordinates of the player when dead
- Custom message
- Added command feature /coordinates [on/off]

## Command
Commands | Default | Permission
--- | --- | ---
`/coordinates` | Op | azusacoordinates.command

## Configuration
```yaml
# AzusaCoordinates Configuration

# Show last death coordinates on respawn
show_death_coordinates: true

# Message when coordinates are enabled
coordinates_enabled_message: "§aCoordinates Successfully Activated for All Worlds"

# Message when coordinates are disabled
coordinates_disabled_message: "§cCoordinates Successfully Disabled for All Worlds"

# Last death message
last_death_message: "Last Coordinates When You Died: [%s] [%d, %d, %d]"

## %s = World
## %d = Player Coordinates
```
