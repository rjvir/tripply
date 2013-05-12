# FLIGHTFINDER IS NOW ITS OWN REPO IN jtdaugh/FF

# #!/usr/bin/env ruby

# require 'rubygems'
# require 'uri'
# require 'net/http'
# require 'parse-ruby-client'
# require 'active_support/all'
# require 'nokogiri'

# MAX_PER_CITY = 9

# parseID = "DaJkkAOKSFVxqPbI7gyPluuqRWkUgGIgzDzMJhUD"
# parseREST = "Hy5vzkrQd3U4ksKVjn0yW697KWFCyaOCVznLe9Qo"
# FF_ADMIN_USER_ID = "flightfinder"
# FF_ADMIN_PASSWORD = "fuckkayak"


# Parse.init :application_id => parseID,
#            :api_key        => parseREST

# HOTEL_KEYS = ["cbq2aqrfn9k93tw7e23x934m","hf8br596p8sy9ahvc2yu466a","d5tcnqy2ncb8bv5cdkss7r47","cq9t5q9r8z9cnym5syrp7zgg","w4k755s7wsxm4dks5xm74dfe","bynsqz35cd6qjr9yncw7njb6"]

# class Cities
#   @@images = Array.new #static CityImages array [[apt,url],[apt,url],...]

#   attr_accessor :aptCode
#   attr_accessor :city
#   attr_accessor :state
#   attr_accessor :imgUrl

#   def initialize(a,l)
#     @aptCode = a 
#     @city = l.to_s.split(', ')[0]
#     @state = l.to_s.split(', ')[1]  
#     @imgUrl = "unset"  
#   end

#   def getImage
#     if @@images.size == 0
#       Cities.getAllImages
#     end

#     @@images.each do |parseObj|
#       if parseObj["airportCode"] == @aptCode
#         @imgUrl = parseObj["imageUrl"]
#         break
#       end
#     end

#     if @imgUrl == "unset"
#       newImageSearch
#     end
#   end
  
#   def self.getAllImages
#     # Query parse and store into @@images class var
#     cityImagesQuery = Parse::Query.new("CityImages")
#     @@images = cityImagesQuery.get
#   end

#   def newImageSearch
#     # Use an image search api
#     @imgUrl = "http://#{city.gsub(" ",'_')}.jpg.to"
#     newParseImg = Parse::Object.new("CityImages")
#     newParseImg["airportCode"] = @aptCode
#     newParseImg["imageUrl"] = @imgUrl
#     @@images.push(newParseImg)
#     newParseImg.save
#   end

# end

# class Deal
  
#   attr_accessor :origin
#   attr_accessor :destination
#   attr_accessor :departDate
#   attr_accessor :returnDate
#   attr_accessor :price
#   attr_accessor :hotelPrice
#   attr_accessor :airline
#   attr_accessor :airLink
#   attr_accessor :hotelLink

#   def initialize(oCode,oCity,dCode,dCity,depart,retrn,p,a)
#     @origin = Cities.new(oCode,oCity)
#     @destination = Cities.new(dCode,dCity)
#     @departDate = DateTime.strptime(depart,"%m/%d/%Y")
#     @returnDate = DateTime.strptime(retrn,"%m/%d/%Y")
#     @price = p
#     @airline = a
#   end

#   def self.newFromKayak(dealXML)
#     newDeal =self.new(dealXML.xpath("kyk:originCode").inner_text,
#                       dealXML.xpath("kyk:originLocation").inner_text,
#                       dealXML.xpath("kyk:destCode").inner_text,
#                       dealXML.xpath("kyk:destLocation").inner_text,
#                       dealXML.xpath("kyk:departDate").inner_text,
#                       dealXML.xpath("kyk:returnDate").inner_text,
#                       dealXML.xpath("kyk:price").inner_text,
#                       dealXML.xpath("kyk:airline").inner_text)    
#   end

#   def tripLength
#     return (returnDate - departDate).to_i
#   end

#   def inFuture?
#     return (departDate - DateTime.now) > 1 ? 1 : 0
#   end

#   def findHotel i

#     hotelQueryUrl = "http://api.ean.com/ean-services/rs/hotel/v3/list?cid=55505&minorRev=16&apiKey=#{HOTEL_KEYS[i % HOTEL_KEYS.size]}&locale=en_US&currencyCode=USD&"
#     hotelQueryUrl << "arrivalDate=#{departDate.strftime("%m/%d/%Y")}&departureDate=#{returnDate.strftime("%m/%d/%Y")}&room=1&"
#     cityStr = destination.city.gsub(" ",'%20')
#     stateStr = destination.state.gsub(" ","")
#     hotelQueryUrl << "destinationString=#{cityStr + "," + stateStr}&numberOfResults=1&sort=PRICE"
#     hotelUri = URI(hotelQueryUrl)
   
#     hotelData = Net::HTTP.get(hotelUri)
#     parsed = JSON.parse(hotelData)

#     if (parsed && 
#         parsed["HotelListResponse"] && 
#         parsed["HotelListResponse"]["HotelList"] && 
#         parsed["HotelListResponse"]["HotelList"]["HotelSummary"] &&
#         parsed["HotelListResponse"]["HotelList"]["HotelSummary"]["RoomRateDetailsList"] && 
#         parsed["HotelListResponse"]["HotelList"]["HotelSummary"]["RoomRateDetailsList"]["RoomRateDetails"] &&
#         parsed["HotelListResponse"]["HotelList"]["HotelSummary"]["RoomRateDetailsList"]["RoomRateDetails"]["RateInfos"] &&
#         parsed["HotelListResponse"]["HotelList"]["HotelSummary"]["RoomRateDetailsList"]["RoomRateDetails"]["RateInfos"]["RateInfo"] &&
#         parsed["HotelListResponse"]["HotelList"]["HotelSummary"]["RoomRateDetailsList"]["RoomRateDetails"]["RateInfos"]["RateInfo"]["ChargeableRateInfo"] &&
#         parsed["HotelListResponse"]["HotelList"]["HotelSummary"]["RoomRateDetailsList"]["RoomRateDetails"]["RateInfos"]["RateInfo"]["ChargeableRateInfo"]["@maxNightlyRate"]) then
#       @hotelPrice = parsed["HotelListResponse"]["HotelList"]["HotelSummary"]["RoomRateDetailsList"]["RoomRateDetails"]["RateInfos"]["RateInfo"]["ChargeableRateInfo"]["@maxNightlyRate"]
#       @hotelPrice = @hotelPrice.to_i
#     end
#     sleep(1.1 / (HOTEL_KEYS.size * 5))
    
#     @hotelLink = "http://www.expedia.com/Hotel-Search#destination=#{destination.city + "," + destination.state}&startDate=#{departDate.strftime("%m/%d/%Y")}&endDate=#{returnDate.strftime("%m/%d/%Y")}&adults=1&star=0&sort=price"

#   end

#   def genAirLink
#     #just concatenation
#     @airLink = "http://www.expedia.com/Flights-Search?trip=roundtrip&leg1=from:#{origin.aptCode},to:#{destination.aptCode},departure:#{departDate.strftime("%m/%d/%Y")}TANYT&leg2=from:#{destination.aptCode},to:#{origin.aptCode},departure:#{returnDate.strftime("%m/%d/%Y")}TANYT&passengers=children:0,adults:1,seniors:0,infantinlap:Y&options=cabinclass:coach,nopenalty:N,sortby:price&mode=search"
#   end

# end

# def kayakRssRequest(origin, today, cityDeals, maxDeals) 

#   possibleDeals = Array.new
#   uri = URI("http://www.kayak.com/h/rss/buzz?code=#{origin}&tm=#{today.strftime("%Y%m")}")
#   xml_data = Net::HTTP.get(uri)
#   xml_doc  = Nokogiri::XML(xml_data)
#   xml_doc.xpath("//item").each do |deal|
#     possibleDeals.push(Deal.newFromKayak(deal))
#   end
#   possibleDeals.sort do |a,b| a.departDate <=> b.departDate end
#   dealsAdded = 0
#   possibleDeals.each do |deal|
#     len = deal.tripLength
#     future = deal.inFuture?
#     if (len > 2 && len < 12 && future == 1) then
#       deal.genAirLink
#       deal.destination.getImage
#       deal.findHotel(dealsAdded)
#       cityDeals.push(deal)
#       dealsAdded += 1
#       if (dealsAdded >= maxDeals) 
#         break
#       end
#     end
#   end
# end

# def deleteOldDeals city
#   toDeleteQuery = Parse::Query.new("Deals")
#   toDeleteQuery.eq("originCode",city)
#   dealsToDelete = toDeleteQuery.get
#   deleteBatch = Parse::Batch.new
#   dealsToDelete.each do |oldDeal|
#     deleteBatch.delete_object(oldDeal)
#   end
#   puts "Deleting #{dealsToDelete.size} old deals"
#   deleteBatch.run!
#   return dealsToDelete.size
# end

# def pushNewDeals deals
#   batch = Parse::Batch.new
#   deals.each do |deal|
#     parseObj = Parse::Object.new("Deals")
#     parseObj["airline"] = deal.airline
#     parseObj["departDate"] = deal.departDate.strftime("%m/%d/%Y")
#     parseObj["destCode"] = deal.destination.aptCode
#     parseObj["destLocation"] = (deal.destination.city + ", " + deal.destination.state)
#     parseObj["hotel_link"] = deal.hotelLink
#     parseObj["hotel_price"] = deal.hotelPrice
#     parseObj["imageUrl"] = deal.destination.imgUrl
#     parseObj["link"] = deal.airLink
#     parseObj["originCode"] = deal.origin.aptCode
#     parseObj["originLocation"] = (deal.origin.city + ", " + deal.origin.state)
#     parseObj["price"] = deal.price
#     parseObj["returnDate"] = deal.returnDate.strftime("%m/%d/%Y")
#     batch.create_object(parseObj)
#   end
#   puts "Pushing #{deals.size} new deals"
#   batch.run!
#   return deals.size
# end


# if __FILE__ == $0
#   ffUser = Parse::User.authenticate(FF_ADMIN_USER_ID, FF_ADMIN_PASSWORD)

#   originQuery = Parse::Query.new("Cities")
#   originQuery.eq("active",true)
#   originCities = originQuery.get
#   totalDeleted = 0
#   totalAdded = 0
#   originCities.each do |city|
#     puts "\nSearching for: #{city["airport_code"]}"
#     cityDeals = Array.new
#     kayakRssRequest(city["airport_code"],DateTime.now, cityDeals, MAX_PER_CITY)
#     dealsAdded = cityDeals.size
#     puts "#{city["airport_code"]} - #{DateTime.now.strftime("%B")}: Found #{dealsAdded} deals"
    
#     #do we need a second month?
#     if (dealsAdded < MAX_PER_CITY)
#       nextMonth = DateTime.now + 1.month
#       kayakRssRequest(city["airport_code"],nextMonth, cityDeals, MAX_PER_CITY - dealsAdded)
#       additionalDeals = cityDeals.size - dealsAdded
#     puts "#{city["airport_code"]} - #{nextMonth.strftime("%B")}: Found #{additionalDeals} deals"
#     end
#     #do we need a third month?
#     dealsAdded = cityDeals.size
#     if (dealsAdded < MAX_PER_CITY)
#       thirdMonth = DateTime.now + 2.month
#       kayakRssRequest(city["airport_code"],thirdMonth, cityDeals, MAX_PER_CITY - dealsAdded)
#       additionalDeals = cityDeals.size - dealsAdded
#     puts "#{city["airport_code"]} - #{thirdMonth.strftime("%B")}: Found #{additionalDeals} deals"
#     end

#     totalDeleted += deleteOldDeals(city["airport_code"])
#     totalAdded += pushNewDeals(cityDeals)
#   end

#   puts "\n-------------- Flight Finder Finished --------------"
#   puts "#{totalDeleted} deals deleted" 
#   puts "#{totalAdded} deals added"

# end
