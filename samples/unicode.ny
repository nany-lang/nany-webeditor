func readHaiku(cref filename)
{
   var haiku = "古池や蛙飛び込む水の音\n富士の風や扇にのせて江戸土産";
   print("haiku:\n\(haiku)\n\n");
   std.io.file.rewrite(filename, haiku);

   var file = new std.io.File(ro: filename);
   var lineindex = 0u;
   while not file.eof do
   {
       lineindex += 1u;
       ref line = file.readline();
       print("\(lineindex): \(line)\n");
   }
}

func main
{
   readHaiku("/root/haiku.txt");
}