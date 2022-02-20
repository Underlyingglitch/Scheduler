const Discord = require("discord.js");
const config = require("./config.json");

const prefix = "!";

const bot = new Discord.Client({intents: ["GUILDS", "GUILD_MESSAGES"]});

bot.login(config.BOT_TOKEN);
console.log("Bot started");

bot.on("messageCreate", function(message) {
    if (message.author.bot) return;
    if (!message.content.startsWith(prefix)) return;

    const commandBody = message.content.slice(prefix.length);
    const args = commandBody.split(' ');
    const command = args.shift().toLowerCase();
    const notify_channel = config['channels'][message.guildId.toString()]['notify'];

    
});