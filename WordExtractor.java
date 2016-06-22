package extractorwords;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

import org.apache.tika.Tika;
import org.apache.tika.exception.TikaException;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.parser.jpeg.JpegParser;
import org.apache.tika.parser.microsoft.OfficeParser;
import org.apache.tika.parser.microsoft.ooxml.OOXMLParser;
import org.apache.tika.parser.pdf.PDFParser;
import org.apache.tika.parser.pdf.PDFParserConfig;
import org.apache.tika.parser.txt.TXTParser;
import org.apache.tika.sax.BodyContentHandler;

import org.xml.sax.SAXException;

public class WordExtract {

   public static void main(final String[] args) throws IOException,TikaException, SAXException {
	   try{
	   FileWriter file_writer = new FileWriter("/Users/words.txt");
		 
	    
	   final File folder_path = new File("/Users/download");
	   ArrayList<String> filePathList = new ArrayList<String>();
	   listFilesForFolder(folder_path, filePathList);
	   for(String str: filePathList)
	   {
	   try{
	  File file = new File(folder_path+"/"+str);
	   Tika tika = new Tika();
	   String content_type = tika.detect(file);
	   
	   
	   BodyContentHandler handler = new BodyContentHandler();
	      Metadata metadata = new Metadata();
	      FileInputStream inputstream = new FileInputStream(file);
	      ParseContext pcontext = new ParseContext();
	     
	   if(content_type.contains("application/pdf"))
	   {
		      //parsing the document using PDF parser
		      PDFParser pdfparser = new PDFParser(); 
		      pdfparser.parse(inputstream, handler, metadata,pcontext);
	   }
	   else if(content_type.contains("htm"))
	   {
		   HtmlParser htmlparser = new HtmlParser();
		   htmlparser.parse(inputstream, handler, metadata,pcontext);
	   }
	   else if(content_type.equals("application/msword"))
	   {
		   
		  OfficeParser  msofficeparser = new OfficeParser (); 
		   
		   msofficeparser.parse(inputstream, handler, metadata,pcontext); 
	   }
	   else if(content_type.equals("application/vnd.openxmlformats-officedocument.wordprocessingml.document"))
	   {
		   OOXMLParser  msofficeparser2 = new OOXMLParser ();
		   msofficeparser2.parse(inputstream, handler, metadata,pcontext); 
	   }
     
     file_writer.append(handler.toString());
	    file_writer.append('\n');
	 
     
      String[] metadataInfo = metadata.names();
      
      
      for(String name : metadataInfo) {
        file_writer.append(name+ " : " + metadata.get(name));
	    file_writer.append('\n');
      }
	   }
      catch(Exception e)
      {
    	  e.printStackTrace();
      }
      
	 } 
	   
	   file_writer.flush();
	    file_writer.close();
   }  
	    catch(Exception e)
	    {
	    	e.printStackTrace();
	    }


	    
   }

   public static void listFilesForFolderPath(final File folder_path, ArrayList<String> filePathList) {
	    for (final File fileEntry : folder_path.listFiles()) {
	        
	         filePathList.add(fileEntry.getName());
	      
	    }
	}
   
   

}