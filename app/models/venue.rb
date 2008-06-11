class Venue < ActiveRecord::Base
  has_many :events

  validates_size_of     :short_name, :locality, :within => 1..50
  validates_size_of     :name, :organization, :within => 1..150
  validates_uniqueness_of :short_name, :name
end
