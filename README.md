# <img src= http://arenateam.com/wp-content/uploads/2016/04/ARena-team-2.png >
# TeamArena ⚔ 
One plugin minigame for pocketmine-pmmp

# How to setup ? 🛠
- You need install plugins: ```EconomyAPI, KillCounter```;
- This is config for arenas(arenas.yml):
```
arena: 
        spawn_random: ["243,66,244", "264,67,230"]
        #protect_areas: [["123,126,67", "232,126,57"]]
        per_team: 11
        finish_points: 100
        restarting: false
        name: "arena"              
        level: "arena"
        name_data: "arena"
        status: "enable"  
        joinsign: ["255,5,260", "world"]        
```
- Usage: /mypos and /blockpos to check pos for action
- You need to fill in important data such as ```spawn_random``` and ```joinsign``` so that the system identifies and creates a coliseum.
- ```per_team``` it is the score each team puts the largest.
- And all more you need type it!
- Design text in the sign at config(config.yml):
```
---
# Support: EconomyAPI
economy: "EconomyAPI"

# Sign
line_1: "[TeamArena]"
line_2: "%map"
line_3: "%slots/%maxslots"
line_4: "Tap to join"

# Message
arena_is_restarting: "Arena is restarting..."  
arena_is_full: "Arena is full!"
joined: "You joined the game!"
kill_by: "%player was killed by %killer"
chest_refilled: "All chests were refiled!"
team_won: "Team %team won the game!"
...
```

# Support 💁‍♂️
Thanks for downloading!
- Email: pnam5005@gmail.com

