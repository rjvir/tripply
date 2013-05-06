#!/usr/bin/env ruby

# FLIGHTFINDER
# get list of origin cities from parse
# get cityImages list from parse
# for each origin (around 50)
# => count # of deals (n_before)
# => do search for new deals [1]
# => append new deals
# => delete oldest n_before entries for city

# [1] DEAL SEARCH
# create kayak rss url from origin code & current month
# for each result:
# => check that it is in the future
# => check that 10 > length > 1
# => For destination city, check cityImages
# => If no image yet for city, do image search jpg.to or meme.co (or boss api)
# => Do hotel search based on destCity and dates
# => add to insert query
# if less than 9 results, query a second month

#images will be array of [airportCode, url]

class Cities
  @@images = Array.new #static CityImages array [[apt,url],[apt,url],...]

  attr_accessor :aptCode
  attr_accessor :city
  attr_accessor :state
  attr_accessor :imgUrl

  def initialize a c s
    @aptCode = a 
    @city = c
    @state = s    
  end

  def getImage
    images.each do |pair|
      if pair.include? @aptCode
        @imgUrl = pair[1]
        imgUrl
      end
    end
  end
  
  def getAllImages
    # Query parse and store into @@images class var

  end

  def newImageSearch
    # Use an image search api

  end

end

class Deal
  
  attr_accessor :origin
  attr_accessor :destination
  attr_accessor :departDate
  attr_accessor :returnDate
  attr_accessor :price
  attr_accessor :airline
  attr_accessor :airLink
  attr_accessor :hotelLink

  def dateFromString s

  end

  def initialize oCode oCity oState dCode dCity dState depart retrn p a
    @origin = Location.new oCode oCity oState
    @destination = Location.new dCode dCity dState
    destination.getImage
    @departDate = DateTime.parse(depart)
    @returnDate = DateTime.parse(retrn)
    @price = p
    @airline = a
  end

  def tripLength d r

  end

  def inFuture? date

  end

  def genHotelLink
  
  end

  def genAirLink

  end

end


if __FILE__ == $0





end
